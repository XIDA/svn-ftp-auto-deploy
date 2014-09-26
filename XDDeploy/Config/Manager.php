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

		/**
		 *	Name of the directorie containing config relevant files
		 */
		const CONFIG_DIRNAME = 'configs';

		/**
		 *	Extension of config/preset files
		 */
		const CONFIG_EXTENSION = 'php';

		/**
		 *	Base name of the preset files
		 */
		const PRESET_FILE_BASE = 'preset_';

		/**
		 *	Base name of the config files
		 */
		const CONFIG_FILE_BASE = 'config_';

		/**
		 *	Stores all files as \RecursiveDirectoryIterator Objects
		 *	@var \RecursiveIteratorIterator
		 */
		private static $files;

		/**
		 *	Stores config files
		 *	array(
		 *		configname => \RecursiveDirectoryIterator
		 *	)
		 *
		 *	@var array
		 */
		private static $configFiles;

		/**
		 *	Stores preset files
		 *	array(
		 *		configname => \RecursiveDirectoryIterator
		 *	)
		 *
		 *	@var array
		 */
		private static $presetFiles;


		/**
		 *	Getter for self::$configFiles
		 *
		 *	@return array
		 */
		public static function getConfigFiles() {
			if(!self::$configFiles) {
				self::getFiles();
			}
			return (array) self::$configFiles;
		}

		/**
		 *	Getter for self::$presetFiles
		 *
		 *	@return array
		 */
		public static function getPresetFiles() {
			if(!self::$presetFiles) {
				self::getFiles();
			}
			return (array) self::$presetFiles;
		}

		/**
		 *	Search for a config/preset file by name.
		 *	If not found let the user now the possible options
		 *
		 *	@param	string		$name		File name
		 *	@param	string		$type		PRESET_FILE_BASE or CONFIG_FILE_BASE
		 *
		 *	@return Full file path as string
		 */
		private static function getFileByName($name, $type) {
			// build filename
			$file	= $type . $name . EXT;

			// select files according to the type
			$files	= ($type == self::CONFIG_FILE_BASE) ? self::getConfigFiles() : self::getPresetFiles();

			// search for the config name
			foreach($files as $fileObject) {
				if($fileObject->getBasename() == $file) {
					return $fileObject->getPathname();
				}
			}

			// if no file is found output possible values to the console
			$possibleValues = PHP_EOL . ' -c ' . implode(PHP_EOL . ' -c ', array_keys($files));
			Logger::n('No or invalid config/preset paramaeter');
			Logger::n('Choose on of the following configs/presets:' . $possibleValues);
			die();
		}


		/**
		 *	Searches for a config file and creates a new Config object
		 *
		 *	@param	string		$name		Config file name
		 *
		 *	@return array of Configs
		 */
		public static function getConfigByName($name) {
			// search for the filename in the config files
			$file			= self::getFileByName($name, self::CONFIG_FILE_BASE);

			// load the config file
			Logger::configInfo('Loading config "' . $file . '" ...');
			$configArray	= require_once($file);

			// array for loop is needed
			$configs		= !is_array($configArray[0]) ? array($configArray) : $configArray;

			// store all configs in a array
			$deployConfigs  = array();
			foreach($configs as $config) {
				$config = new Config($config);
				$deployConfigs[] = $config;
				Logger::configNote('Loaded Config with name ' . $config->getName());
			}
			return $deployConfigs;
		}

		/**
		 *	Get a config preset by a specified name
		 *
		 *	@param	string		$name		Preset file name
		 *
		 *	@return array
		 */
		public static function getPresetByName($name) {
			// search for the filename in the config files
			$file = self::getFileByName($name, self::PRESET_FILE_BASE);
			return new Config(require_once($file), true);
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
						$name = str_replace(self::CONFIG_FILE_BASE, '', $fileObject->getBasename('.' . $fileObject->getExtension()));
						self::$configFiles[$name] = $fileObject;
					} else if(strpos($filename, self::PRESET_FILE_BASE) !== false) {
						$name = str_replace(self::PRESET_FILE_BASE, '', $fileObject->getBasename('.' . $fileObject->getExtension()));
						self::$presetFiles[$name] = $fileObject;
					}
				}
			}
			return self::$files;
		}
	}
?>