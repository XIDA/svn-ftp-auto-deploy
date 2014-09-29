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
		 *	@param	boolean		$preset		Is this a preset configuration?
		 *
		 *	@return \XDDeploy\Config\Config
		 */
		public function __construct($config, $preset = false) {
			$this->config = $config;
			$this->mergeWithPreset();

			$this->validateConfig();

			if(!$preset || isset($this->config['ftp'])) {
				$this->ftp = new Ftp($this->config['ftp']);
			}
			if(!$preset || isset($this->config['svn'])) {
				$this->svn = new Svn($this->config['svn']);
			}

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
			if(isset($this->config['preset'])) {
				return $this->config['preset'];
			}

			return null;
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