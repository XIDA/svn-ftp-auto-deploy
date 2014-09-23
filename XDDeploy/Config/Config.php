<?php
	namespace XDDeploy\Config;
	use XDDeploy\Logger;

	/**
	 * 	Deploy Configuration
	 *
	 * 	@author XIDA
	 */
	class Config {

		/**
		 *	FTP Configuration
		 *	@var Ftp
		 */
		public $ftp;

		/**
		 *	SVN Configuration
		 *	@var Svn
		 */
		public $svn;

		/**
		 *	Conig from file
		 *	@var array
		 */
		private $config;

		/**
		 *
		 *	@param	string		$name		Name of the config
		 *
		 *	@return \XDDeploy\Config\Config
		 */
		public function __construct($name) {
			$file = $this->checkFile($name);

			$config = require_once($file);
			$this->config = $config;
			$this->validateConfig();

			$this->ftp = new Ftp($config['ftp']);
			$this->svn = new Svn($config['svn']);

			return $this;
		}

		/**
		 *	Validate all required paramaters
		 */
		private function validateConfig() {
			//
		}

		/**
		 *
		 * @param type $name
		 * @return \XDDeploy\Config\RecursiveIteratorIterator
		 */
		private function checkFile($name) {
			$file = ROOT . 'configs/config.' . $name . '.php';
			if(!file_exists($file)) {
				$path	 = ROOT . 'configs';
				$objects =	new \RecursiveIteratorIterator(
						new \RecursiveDirectoryIterator(
							$path,
							\FilesystemIterator::SKIP_DOTS
						),
					\RecursiveIteratorIterator::SELF_FIRST
				);

				Logger::n(PHP_EOL . 'No or invalid config file paramaeter. Choose on of the following configs as first parameter:');
				foreach($objects as $file) {
					Logger::n(' - ' . str_replace('config.', '', $file->getBasename('.' . $file->getExtension())));
				}
				die();
			}
			return $file;
		}

		/**
		 *	Get current name
		 *
		 *	@return string
		 */
		public function getName() {
			return $this->config['name'];
		}

		/**
		 *	Get version file name
		 *
		 *	@return string
		 */
		public function getVersionFile() {
			return $this->config['version_file'];
		}

		/**
		 *	Debug mode
		 *
		 *	@return boolean
		 */
		public function isDebug() {
			return (boolean) $this->config['debug'];
		}

		/**
		 *	Logging mode
		 *
		 *	@return boolean
		 */
		public function isVerbose() {
			return (boolean) $this->config['verbose'];
		}
	}
?>