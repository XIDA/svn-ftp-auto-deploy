<?php
	namespace XDDeploy\Config;
	use XDDeploy\Utils\Logger;

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
			$this->mergeWithPreset();
			$this->validateConfig();

			$this->ftp = new Ftp($this->config['ftp']);
			$this->svn = new Svn($this->config['svn']);

			return $this;
		}

		/**
		 *	Merge the config with a preset
		 */
		private function mergeWithPreset() {
			if($this->getPreset()) {
				$preset			= Manager::getPresetByName($this->getPreset());
				$this->config	= array_replace_recursive($preset->getConfig(), $this->config);
			}
		}

		/**
		 *	Validate all required paramaters
		 */
		private function validateConfig() {
			if(!$this->getName()) {
				Logger::configError("Property 'name' is required.");
			}
		}

		/**
		 *	Get the array from file
		 *
		 *	@return array
		 */
		public function getConfig() {
			return $this->config;
		}

		/**
		 *	Get preset name
		 *
		 *	@return string
		 */
		public function getPreset() {
			return $this->config['preset'];
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
		 *	Get version file name.
		 *	Default is 'deploy.ver'
		 *
		 *	@return string
		 */
		public function getVersionFile() {
			return $this->config['version_file'] ?: 'deploy.ver';
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