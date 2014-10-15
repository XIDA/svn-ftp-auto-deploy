<?php
	namespace XDDeploy;
	use XDUtils\Logger;
	use XDUtils\CLI;
	use XDTranslations\Translations;

	/**
	 * 	Deploy
	 *
	 * 	@author XIDA
	 */
	class Deploy {

		/**
		 *	Deploy for config
		 *
		 *	@param	string		$name			Name of the configuration
		 *	@param	int			$version		Version to deploy
		 */
		public function __construct($name = null, $version = null) {
			// setup translations
			Translations::setPath(dirname(__FILE__) . DS . 'resources');

			Logger::info(Translations::get('welcome'));

			$configs	= Config\Manager::getConfigByName($name);

			foreach($configs as $config) {
				Logger::info(Translations::get('deploy_start', array($config->getName())));
				$this->executeUrls($config->getExecuteBefore());
				$this->deploy($config, $version);
				$this->executeUrls($config->getExecuteAfter());
				Logger::info(Translations::get('deploy_end', array($config->getName())));
			}
		}

		/**
		 *	Execute URLs
		 *
		 *	@param	array			$commands
		 */
		private function executeUrls($commands) {
			$urls = filter_var_array($commands, FILTER_VALIDATE_URL);
			foreach($urls as $url) {
				$result = file_get_contents($url);
				//L ogger::n($result);
			}
		}

		/**
		 *	Deploy to ftp server
		 *
		 *	@param	Config\Config	$config
		 *	@param	string			$version	Default: the newest version.
		 */
		private function deploy(Config\Config $config, $version = null) {
			$fs		= new FileSystem($config);
			$svn	= new Svn($fs, $config);
			$ftp	= new Ftp($fs, $config);

			// get the current active revisions
			$ftpVer			= $ftp->getCurrentVersion();
			$svnLatestVer	= $svn->getCurrentVersion();

			if(!isset($ftpVer) || $ftpVer === false) {
				Logger::warning(Translations::get('version_ftp_not_found', array($config->getVersionFile())));
				if(CLI::userInput(array('y', 'yes', 1)) === false) {
					Logger::fatalError("ABORTING!");
				}
				$ftpVer = 0;
			}

			if(isset($version)) {
				Logger::warning(Translations::get('version_input', array($svnLatestVer, $config->getName())));

				// wait for the user to input the version
				$version	= CLI::userInput(range(1, $svnLatestVer));
				if($version === false) {
					Logger::fatalError(Translations::get('version_not_in_range', array($svnLatestVer)));
				}
			} else {
				$version = $svn->getCurrentVersion();
			}

			Logger::info(Translations::get('version_ftp', array($ftpVer)));
			Logger::info(Translations::get('version_svn', array($version)));

			if ($config->isDebug()) {
				var_dump($ftpVer, $version, $config);
				exit;
			}

			if ($version != $ftpVer) {
				Logger::notice('collecting changed files..');
				$changes = $svn->checkoutChanges($version, $ftpVer);

				Logger::warning('found ' . (count($changes['files'])) . ' files / directories that changed and ' . (count($changes['delFiles'])) . ' files to delete');

				// Create a .ver file
				$fs->addSvnVersion($version);

				$changes['files'][] = $config->getVersionFile();

				$ftp->putChanges($changes);
				Logger::success('done');
			} else {
				Logger::success('Nothing to do - Up to date');
			}

			$fs->removeTempFolder();
			return true;
		}
	}
?>