<?php
	namespace XDDeploy;
	use XDUtils\Logger;
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
		public function __construct($name, $version = false) {
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
		private function deploy(Config\Config $config, $version = false) {
			$fs		= new FileSystem($config);
			$svn	= new Svn($fs, $config);
			$ftp	= new Ftp($fs, $config);


			$ftpVer = $ftp->getCurrentVersion();
			$svnLatestVer = $svn->getCurrentVersion();

			if($ftpVer == "") {
				Logger::fatalError('error: could not get version from FTP');
			}

			if($ftpVer  == -1) {
				Logger::warning('No "' . $config->getVersionFile() . '" file found on the ftp, is this your first commit? (type y to continue)');
				$handle = fopen ("php://stdin","r");
				$line = fgets($handle);
				if(trim($line) != 'y'){
					Logger::fatalError("ABORTING!");
				}
				echo PHP_EOL;
				$ftpVer = 0;
			}

			if($version) {
				if($version > $svnLatestVer) {
					Logger::fatalError('target revison is greater than latest svn revision ' . $svnLatestVer);
				}
			} else {
				$version = $svn->getCurrentVersion();
			}

			Logger::info('ftp version: ' . $ftpVer);
			Logger::info('svn target version: ' . $version);

			if ($config->isDebug()) {
				var_dump($ftpVer, $version, $config);
				exit;
			}

			if ($version != $ftpVer) {
				Logger::note('collecting changed files..');
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
		}
	}
?>