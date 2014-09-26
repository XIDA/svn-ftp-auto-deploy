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
			$configs	= Config\Manager::getConfigByName($name);

			foreach($configs as $config) {
				echo PHP_EOL;
				Logger::e('--- Deploy - ' . $config->getName() . ' - Start ---');
				$this->deploy($config, $version);
				Logger::e('--- Deploy - ' . $config->getName() . ' - End -----');
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
				Logger::e('error: could not get version from FTP');
				exit;
			}

			if($ftpVer  == -1) {
				Logger::i('No ' . $config->getVersionFile() . ' file found on the ftp, is this your first commit? (type y to continue)');
				$handle = fopen ("php://stdin","r");
				$line = fgets($handle);
				if(trim($line) != 'y'){
					Logger::e("ABORTING!");
					exit;
				}
				echo PHP_EOL;
				$ftpVer = 0;
			}

			if($version) {
				if($version > $svnLatestVer) {
					Logger::e('target revison is greater than latest svn revision ' . $svnLatestVer);
					exit;
				}
			} else {
				$version = $svn->getCurrentVersion();
			}

			Logger::i('ftp version: ' . $ftpVer);
			Logger::i('svn target version: ' . $version);

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
			}

			$fs->removeTempFolder();
		}
	}
?>