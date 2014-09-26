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
		 *	@param	string		$name
		 *	@param	int			$version
		 */
		public function __construct($name, $version = false) {
			$configs	= Config\Manager::getConfigByName($name);

			foreach($configs as $config) {
				$this->deploy($config, $version);
			}
		}

		/**
		 *	Deploy
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
				$targetVer = $version;
				if($targetVer > $svnLatestVer) {
					Logger::e('target revison is greater than latest svn revision ' . $svnLatestVer);
					exit;
				}
			} else {
				$targetVer = $svn->getCurrentVersion();
			}

			Logger::i('ftp version: ' . $ftpVer);
			Logger::i('svn target version: ' . $targetVer);

			if ($config->isDebug()) {
				var_dump($ftpVer, $targetVer, $config);
				exit;
			}

			if ($targetVer != $ftpVer) {
				Logger::i('collecting changed files');
				$changes = $svn->checkoutChanges($targetVer, $ftpVer);

				Logger::i('found ' . (count($changes['files'])) . ' files / directories that changed and ' . (count($changes['delFiles'])) . ' files to delete');

				// Create a .ver file
				$fs->addSvnVersion($targetVer);

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