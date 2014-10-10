<?php
	namespace XDDeploy;
	use XDDeploy\Utils\Logger;

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
			Logger::n('Welcome to the XIDA SVN-FTP Deploy tool' . PHP_EOL);

			$configs	= Config\Manager::getConfigByName($name);

			foreach($configs as $config) {
				echo PHP_EOL;
				Logger::fileLog('');
				Logger::i('--- Deploy - ' . $config->getName() . ' - Start ---');
				Logger::fileLog('Deploy - ' . $config->getName() . ' - Start');
				$this->executeUrls($config->getExecuteBefore());
				$this->deploy($config, $version);
				$this->executeUrls($config->getExecuteAfter());
				Logger::fileLog('Deploy - ' . $config->getName() . ' - End');
				Logger::i('--- Deploy - ' . $config->getName() . ' - End ---');
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
				Logger::abort('error: could not get version from FTP');
				Logger::fileLog('error: could not get version from FTP');
			}

			if($ftpVer  == -1) {
				Logger::i('No "' . $config->getVersionFile() . '" file found on the ftp, is this your first commit? (type y to continue)');
				$handle = fopen ("php://stdin","r");
				$line = fgets($handle);
				if(trim($line) != 'y'){
					Logger::abort("ABORTING!");
					Logger::fileLog('ABORTING');
				}
				echo PHP_EOL;
				$ftpVer = 0;
			}

			if($version) {
				if($version > $svnLatestVer) {
					Logger::abort('target revison is greater than latest svn revision ' . $svnLatestVer);
					Logger::fileLog('target revison is greater than latest svn revision ' . $svnLatestVer);
				}
			} else {
				$version = $svn->getCurrentVersion();
			}

			Logger::i('ftp version: ' . $ftpVer);
			Logger::fileLog('ftp version: ' . $ftpVer);

			Logger::i('svn target version: ' . $version);
			Logger::fileLog('svn target version: ' . $version);

			if ($config->isDebug()) {
				var_dump($ftpVer, $version, $config);
				exit;
			}

			if ($version != $ftpVer) {
				Logger::i('collecting changed files');
				$changes = $svn->checkoutChanges($version, $ftpVer);

				Logger::i('found ' . (count($changes['files'])) . ' files / directories that changed and ' . (count($changes['delFiles'])) . ' files to delete');

				// Create a .ver file
				$fs->addSvnVersion($version);

				$changes['files'][] = $config->getVersionFile();

				$ftp->putChanges($changes);
				Logger::i('done');
			} else {

				Logger::i('Nothing to do - Up to date');
				Logger::fileLog('Nothing to do - Up to date');
			}

			$fs->removeTempFolder();
		}
	}
?>