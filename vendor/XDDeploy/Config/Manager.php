<?php
	namespace XDDeploy\Config;
	use XDUtils\File;
	use XDUtils\CLI;
	use XDUtils\Logger;
	use XDTranslations\Translations;

	/**
	 * 	Manages configs
	 *
	 * 	@author XIDA
	 */
	class Manager {

		/**
		 *	Name of the directorie containing config relevant files
		 */
		const DIRNAME = 'configs';

		/**
		 *	Extension of config/preset files
		 */
		const FILE_EXTENSION = 'php';

		/**
		 *	Base name of the preset files
		 */
		const PRESET_NAME = 'preset';

		/**
		 *	Base name of the config files
		 */
		const CONFIG_NAME = 'config';

		/**
		 *	Separator between config/preset base and name
		 */
		const NAME_SEPARATOR = '_';

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
		 *	Stores current config name
		 *
		 *	@var string
		 */
		private static $configName = '';

		/**
		 *	Getter for self::$configFiles
		 *
		 *	@return array
		 */
		public static function getConfigFiles() {
			if(!self::$configFiles) {
				self::searchForFiles();
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
				self::searchForFiles();
			}
			return (array) self::$presetFiles;
		}

		/**
		 *	Search for a config/preset file by name.
		 *	If not found let the user now the possible options
		 *
		 *	@param	string		$name		File name
		 *	@param	string		$type		PRESET_NAME or CONFIG_NAME
		 *
		 *	@return Full file path as string
		 */
		private static function getFileByName($name, $type) {
			// build filename
			$file	= $type . self::NAME_SEPARATOR . $name . "." . self::FILE_EXTENSION;

			// select files according to the type
			$files	= ($type == self::CONFIG_NAME) ? self::getConfigFiles() : self::getPresetFiles();

			// search for the config name
			foreach($files as $fileObject) {
				if($fileObject->getBasename() == $file) {
					// save the name if this is a config
					if($type == self::CONFIG_NAME) {
						self::$configName = $name;
					}
					return $fileObject->getPathname();
				}
			}

			// no file is found, so let the user choose the config
			// if there are valid config/preset files
			if(empty($files)) {
				Logger::fatalError(Translations::get('config_no_file_found_for_type', array($type, $name)));
			}
			return self::getFileNameFromInput($files, $type);
		}

		/**
		 *	Get a config file name from user input
		 *
		 *	@param	array		$files		Possible files
		 *	@param	string		$type		PRESET_NAME or CONFIG_NAME
		 *
		 *	@return string
		 */
		private static function getFileNameFromInput($files, $type) {
			// if no file is found output possible values to the console
			$possibleFiles = array_keys($files);
			Logger::userInput(Translations::get('config_not_found', array($type)));
			foreach($possibleFiles as $index => $value) {
				Logger::userInput($index . ' - ' . $value);
			}

			Logger::userInput(Translations::get('config_choose_number', array(sizeOf($possibleFiles) - 1)));
			// wait for the user to select a configuraiton via number
			$input = CLI::userInput(range(0, sizeOf($possibleFiles) - 1));

			// if the choosen file exists
			if($input !== false && isset($possibleFiles[$input])) {
				// let the user confirm the selection
				Logger::userInput(Translations::get('config_confirm_selection', array($type, $possibleFiles[$input])));
				if(CLI::userInput(array('y', 'yes', 1)) === false) {
					Logger::fatalError();
				}
				// save the name if this is a config
				if($type == self::CONFIG_NAME) {
					self::$configName = $possibleFiles[$input];
				}
				return $files[$possibleFiles[$input]];
			}
			Logger::fatalError(Translations::get('config_number_invalid', array($input)));
		}

		/**
		 *	Get the last handled config name
		 *
		 *	@return string
		 */
		public static function getLastConfigName() {
			return self::$configName;
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
			$file		= self::getFileByName($name, self::CONFIG_NAME);

			// remove the directory for the file that is shown in the log
			$configName = str_replace(ROOT . self::DIRNAME . DS, "", $file);
			Logger::debug(Translations::get('config_loading_file', array($configName)));

			// load the config file
			$data		= require_once($file);

			// array for loop is needed
			if(!isset($data[0]) || !is_array($data[0])) {
				$data = array($data);
			}

			// store all configs in a array
			$deployConfigs  = array();
			foreach($data as $config) {
				if(!is_array($config)) {
					Logger::fatalError(Translations::get('config_data_invalid', array($configName)));
				}
				$configObject		= new Config($config);
				$deployConfigs[]	= $configObject;
			}
			return $deployConfigs;
		}

		/**
		 *	Get a config preset by a specified name
		 *
		 *	@param	string		$name		Preset file name
		 *	@param	string		$class		Name of the object, to create a preset for
		 *
		 *	@return a config object, defined by the class parameter
		 */
		public static function getPresetByName($name, $class) {
			// search for the filename in the config files
			$file = self::getFileByName($name, self::PRESET_NAME);
			return new $class(require_once($file), true);
		}

		/**
		 *	Get all config files as \RecursiveDirectoryIterator Objects
		 *	@see http://php.net/manual/de/class.recursivedirectoryiterator.php
		 *
		 *	@return \RecursiveIteratorIterator
		 */
		private static function searchForFiles() {
			if(self::$files) {
				return self::$files;
			}

			// get all files from config dir
			self::$files = File::getFilesRecursive(ROOT . self::DIRNAME);
			// check if there are files
			if(self::$files === false) {
				Logger::fatalError(Translations::get('config_no_file_found', array(ROOT . self::DIRNAME)));
			}

			foreach(self::$files as $filename => $fileObject) {
				// only files with allowed extension
				if($fileObject->getExtension() != self::FILE_EXTENSION) {
					continue;
				}

				// check if a file is a config or preset.
				// save config/preset files in separate arrays
				if(strpos($filename, self::CONFIG_NAME . self::NAME_SEPARATOR) !== false) {
					$name = str_replace(self::CONFIG_NAME . self::NAME_SEPARATOR, '', $fileObject->getBasename('.' . $fileObject->getExtension()));
					self::$configFiles[$name] = $fileObject;
				} else if(strpos($filename, self::PRESET_NAME . self::NAME_SEPARATOR) !== false) {
					$name = str_replace(self::PRESET_NAME . self::NAME_SEPARATOR, '', $fileObject->getBasename('.' . $fileObject->getExtension()));
					self::$presetFiles[$name] = $fileObject;
				}
			}
			// check if we have any config file
			if(empty(self::$presetFiles) && empty(self::$configFiles)) {
				Logger::fatalError(Translations::get('config_no_file_found', array(ROOT . self::DIRNAME)));
			}
			return self::$files;
		}
	}
?>