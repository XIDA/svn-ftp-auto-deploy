<?php
	namespace XDDeploy\Config;
	use XDDeploy\Utils\File;
	use XDDeploy\Utils\Logger;

	/**
	 * 	Manages configs
	 *
	 * 	@author XIDA
	 */
	class Manager {

		private static $files;
		private static $configFiles;
		private static $presetFiles;

		const CONFIG_DIRNAME = 'configs';
		const CONFIG_EXTENSION = 'php';

		const PRESET_FILE_BASE = 'preset_';
		const CONFIG_FILE_BASE = 'config_';

		public static function getConfigFiles() {
			if(!self::$configFiles) {
				self::getFiles();
			}
			return self::$configFiles;
		}

		public static function getPresetFiles() {
			if(!self::$presetFiles) {
				self::getFiles();
			}
			return self::$presetFiles;
		}

		private static function getFileNames(\RecursiveIteratorIterator $files) {
			foreach($files as $filename => $fileObject) {
				
			}
		}

		private static function getFileByName($name, $type) {
			$file	= $type . $name . EXT;
			$files	= ($type == self::CONFIG_FILE_BASE) ? self::getConfigFiles() : self::getPresetFiles();
			foreach($files as $filename => $fileObject) {
				if($fileObject->getBasename() == $file) {
					return $filename;
				}
			}
			return false;
		}


		public static function getConfigByName($name) {
			$file = self::getFileByName($name, self::CONFIG_FILE_BASE);

			if(!$file) {
				Logger::n(PHP_EOL . 'No or invalid -c paramaeter. Choose on of the following configs for the -c parameter:');
				Logger::n($log ?: 'No config available');
				die();
			}
			Logger::configInfo('Loading config "' . $file . '" ...');
			$configArray	= require_once($file);
			$configs = !is_array($configArray[0]) ? array($configArray) : $configArray;

			$deployConfigs = array();

			foreach($configs as $config) {
				$config = new Config($config);
				Logger::configNote('config with name ' . $config->getName());
				$deployConfigs[] = $config;
			}
			return $deployConfigs;
		}

		/**
		 *	Get a config preset by a specified name
		 *
		 *	@param	string		$name
		 *
		 *	@return array
		 */
		public static function getPresetByName($name) {
			$file = self::getFileByName($name, self::PRESET_FILE_BASE);
			if(!$file) {
				Logger::configError('Preset with name "' . $name . '" does not exist.');
				die();
			}
			return new Config(require_once($file));
		}

		/**
		 *	Get all config files as \RecursiveDirectoryIterator Objects
		 *	@see http://php.net/manual/de/class.recursivedirectoryiterator.php
		 *
		 *	@return \RecursiveIteratorIterator
		 */
		private static function getFiles() {
			if(!self::$files) {
				self::$files = File::getFilesRecursive(ROOT . self::CONFIG_DIRNAME);

				foreach(self::$files as $filename => $fileObject) {
					if($fileObject->getExtension() != self::CONFIG_EXTENSION) {
						continue;
					}

					if(strpos($filename, self::CONFIG_FILE_BASE) !== false) {
						self::$configFiles[$filename] = $fileObject;
					} else if(strpos($filename, self::PRESET_FILE_BASE) !== false) {
						self::$presetFiles[$filename] = $fileObject;
					}
				}
			}
			return self::$files;
		}
	}
?>