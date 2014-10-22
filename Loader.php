<?php
	namespace XD;

	// System Start Time
	define('START_TIME', microtime(true));

	// System Start Memory
	define('START_MEMORY_USAGE', memory_get_usage());

	// Extension of all PHP files
	define('EXT', '.php');

	// Directory separator (Unix-Style works on all OS)
	define('DS', '/');

	// root of the vendor folder
	define('LOADER_ROOT', dirname(__FILE__) . DS);

	/**
	 * 	Autoloads namespaced classes
	 *
	 * 	@author XIDA
	 */
	class Loader {
		/**
		 *	Contains paths to namespaced classes
		 *
		 *	@var array
		 */
		private $classes;

		/**
		 *	Contains paths to external namespaces
		 *
		 *	@var array
		 */
		private $namespaces;

		/**
		 *  init XDDeploy Loader
		 *  Register autoloader function
		 *
		 */
		public function __construct(array $classes = null, array $namespaces = null) {
			$this->classes		= $classes;
			$this->namespaces	= $namespaces;
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
			//date_default_timezone_set('UTC');

			// iconv encoding
			// iconv_set_encoding("internal_encoding", "UTF-8");

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
			if (strrpos($className, "\\") !== false) {
				// for namespaced classes, they have a "\" in their class name
				// replace namespace slashes and double slashes with unix slash
				$classFile = str_replace(array('\\', DS . DS), DS, LOADER_ROOT . DS . $className . EXT);

				if(!file_exists($classFile)) {
					// extract namespace from class name
					$namespace = array_slice(explode('\\', $className), 0, -1)[0];

					// look up the other namepsaces base paths
					if(isset($this->namespaces[$namespace])) {
						$classFile = str_replace(array('\\', DS . DS), DS, LOADER_ROOT . DS . $this->namespaces[$namespace] . DS . $className . EXT);
					}
				}
			} else if(isset($this->classes[$className])) {
				// if this is an non namespaced class, look into the classes array
				$classFile = LOADER_ROOT . DS . $this->classes[$className];
			}

			if(isset($classFile) && file_exists($classFile)) {
				// load the class
				require_once($classFile);
			}
		}
	}
?>