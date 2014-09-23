<?php
	namespace XDDeploy;

	/**
	 * 	Autoloads namespaced classes
	 *
	 * 	@author XIDA
	 */
	class Loader {
		/**
		 *  init XDDeploy Loader
		 *  Register autoloader function
		 *
		 */
		public function __construct() {
			$this->registerShutdown();
			$this->registerAutoloader();
			$this->setupPHPSettings();
		}

		/**
		 *	Register XDDeploy shutdown function
		 */
		private function registerShutdown() {
			// php shutdown function
			register_shutdown_function(array($this, 'shutdown'));
		}

		/**
		 *	Register XDDeploy autoload function
		 */
		private function registerAutoloader() {
			// register autoloader
			spl_autoload_extensions(EXT);
			spl_autoload_register(array($this, 'autoload'));
		}

		/**
		 *	Setup PHP settings
		 *	Like Timezone, Encoding, etc..
		 */
		private function setupPHPSettings() {
			// Default timezone of server
			date_default_timezone_set('UTC');

			// iconv encoding
			iconv_set_encoding("internal_encoding", "UTF-8");

			// multibyte encoding
			if(function_exists('mb_internal_encoding')) {
				mb_internal_encoding('UTF-8');
			}
		}

		/**
		 *	PHP Shutdown function
		 *	Called after code execution
		 */
		public function shutdown() {
			//
		}

		/**
		 * 	Automatically includes classes
		 *	Called by PHP
		 *
		 *	@param string		$className		Name of the class to load
		 */
		public function autoload($className) {
			// for namespaced classes, they have a "\" in their class name
			if (strrpos($className, "\\") !== false) {
				// replace namespace slashes and double slashes with unix slash
				$classFile = str_replace(array('\\', DS . DS), DS, ROOT . DS . $className . EXT);
				if(file_exists($classFile)) {
					require_once($classFile);
				}
			}
		}
	}
?>