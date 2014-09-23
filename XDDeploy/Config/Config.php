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
		 *	Setup single config
		 * 
		 *	@param	string		$config		Name of the config
		 *
		 *	@return \XDDeploy\Config\Config
		 */
		public function __construct($config) {
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