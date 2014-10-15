<?php
	namespace XDTranslations;
	use XDUtils\File;

	/**
	 * 	Manages translations
	 *
	 *	To use this you need a folder structure like:
	 *
	 *	- {resources}
	 *		- values-en
	 *			- strings.xml
	 *		- values-de
	 *			- strings.xml
	 *		..
	 *
	 *	Changes path to {resources} with <code>Translations::setPath()</code>.
	 *
	 *	To change the language use <code>Translations::setLangauge(string $language)</code>.
	 *	By default translations for language 'en' are loaded on first <code>Translations::get()</code> call.
	 *
	 *	Use <code>Translations::getTranslations()</code> to get all loaded translations
	 *  or <code>Translations::getTranslationsForLanguage(string $language)</code> to get all translations for a specific language.
	 *
	 *	To get all available languages as array use <code>Translations::getAvailableLanguages()</code>.
	 *
	 * 	@author XIDA
	 */
	class Translations {

		/**
		 *	The xml filename containing strings
		 *
		 *	@var string
		 */
		const XML_FILENAME		= 'strings.xml';

		/**
		 *	Base of the dirname for each language
		 *
		 *	@var string
		 */
		const XML_DIRNAME_BASE	= 'values-';

		/**
		 *	Base file path to search the xmls
		 *
		 *	@var string
		 */
		private static $basePath;

		/**
		 *	Stores loaded translations
		 *
		 *	@var array
		 */
		private static $translations = array();

		/**
		 *	Stores current language
		 *
		 *	@var string
		 */
		private static $language = 'en';

		/**
		 *	Stores all available languages
		 *
		 *	@var array
		 */
		private static $availableLanguages = array();

		/**
		 *	Setup the base path for the language files
		 *
		 *	@param	string		$path
		 */
		public static function setPath($path) {
			self::$basePath = File::getCleanedPath($path . DS);
		}

		/**
		 *	Setup the current language
		 *
		 *	@param	string		$language
		 */
		public static function setLangauge($language) {
			self::$language = $language;
		}

		/**
		 *	Load translations for a language
		 *
		 *	@param	string		$language
		 *
		 *	@throws \InvalidArgumentException if the file for the translation does not exists
		 */
		private static function loadTranslationsForLanguage($language = 'en') {
			$file = self::getFilename($language);

			// check if the file exists
			if(!file_exists($file)) {
				throw new \InvalidArgumentException(__NAMESPACE__ . ': File (' . $file . ') for language (' . $language . ') does not exist!');
			}

			// save language
			self::$language					= $language;
			self::$translations[$language]	= array();

			// load xml file
			$xml		= simplexml_load_file($file);

			// save all items into an array
			foreach($xml->string as $item) {
				$name	= (string) $item->attributes()->name;
				self::$translations[$language][$name] = (string) $item[0];
			}
		}

		/**
		 *	Get the translations array for a language
		 *
		 *	@param	string		$language
		 *
		 *	@return array
		 */
		public static function getTranslationsForLanguage($language) {
			if(!isset(self::$translations[$language])) {
				self::loadTranslationsForLanguage($language);
			}
			return self::$translations[$language];
		}

		/**
		 *	Get a formated translation for a name
		 *	@see http://php.net/manual/de/function.vsprintf.php
		 *
		 *	@param	string			$name		Translation name
		 *	@param	array			$args		Arguments to replace in the translation
		 *
		 *	@return string
		 *
		 *	@throws \InvalidArgumentException if the translation name does not exists in the array
		 */
		public static function get($name, array $args = array()) {
			$translations = self::getTranslationsForLanguage(self::$language);

			// check if the name exists
			if(!isset($translations[$name])) {
				throw new \InvalidArgumentException(__NAMESPACE__ . ': Translation (' . $name . ') does not exist!');
			}
			// use vsprintf to format a string with variables
			return vsprintf($translations[$name], $args);
		}

		/**
		 *	Get the current language
		 *
		 *	@return string
		 */
		public static function getLanguage() {
			return self::$language;
		}

		/**
		 *	Get all translations currently loaded
		 *
		 *	@return array
		 */
		public static function getTranslations() {
			return self::$translations;
		}

		/**
		 *	Build filename for current language
		 *
		 *	@param	string		$language
		 *
		 *	@return string
		 */
		private static function getFilename($language) {
			return self::$basePath . self::XML_DIRNAME_BASE . $language . DS . self::XML_FILENAME;
		}

		/**
		 *	Get all available languages
		 *
		 *	@return array
		 */
		public static function getAvailableLanguages() {
			$dirs		= File::getDirectoryList(self::$basePath);
			foreach($dirs as $dir) {
				if(file_exists($dir . DS . self::XML_FILENAME)) {
					self::$availableLanguages = str_replace(self::XML_DIRNAME_BASE, '', basename($dir));
				}
			}
			return self::$availableLanguages;
		}
	}
?>