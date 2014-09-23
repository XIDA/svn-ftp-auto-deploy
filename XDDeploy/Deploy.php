<?php
	namespace XDDeploy;

	/**
	 * 	Deploy
	 *
	 * 	@author XIDA
	 */
	class Deploy {

		const CONFIG_FILE_BASE = 'config_';
		const CONFIG_DIRNAME = 'configs';

		/**
		 *
		 * @param type $name
		 * @param type $version
		 */
		public function __construct($name, $version = false) {
			$file		= $this->checkConfigFile($name);
			$configArray = require_once($file);

			if(!is_array($configArray[0])) {
				$configs[0] = $configArray;
			} else {
				$configs = $configArray;
			}

			foreach($configs as $config) {
				$this->deploy(new Config\Config($config), $version);
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
				echo 'No ' . $config->getVersionFile() . ' file found on the ftp, is this your first commit? (type y to continue)' . PHP_EOL;
				$handle = fopen ("php://stdin","r");
				$line = fgets($handle);
				if(trim($line) != 'y'){
					echo "ABORTING!\n";
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

		/**
		 *	Check the config file for this deploy
		 *
		 *	@param	string $name		Config name
		 *
		 *	@return string	Config File name
		 */
		private function checkConfigFile($name) {
			$file = self::CONFIG_FILE_BASE . $name . EXT;

			$path	 = ROOT . self::CONFIG_DIRNAME;
			$objects =	new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(
						$path,
						\FilesystemIterator::SKIP_DOTS
					),
				\RecursiveIteratorIterator::SELF_FIRST
			);
			$log = null;

			foreach($objects as $filename => $fileObject) {
				if($fileObject->getBasename() == $file) {
					return $filename;
				} else if(strpos($filename, self::CONFIG_FILE_BASE) !== false && $fileObject->getExtension() == 'php') {
					$log .= ' -c ' . str_replace(self::CONFIG_FILE_BASE, '', $fileObject->getBasename('.' . $fileObject->getExtension())) . PHP_EOL;
				}
			}

			Logger::n(PHP_EOL . 'No or invalid -c paramaeter. Choose on of the following configs for the -c parameter:');
			Logger::n($log ?: 'No config available');
			die();
		}
	}
?>