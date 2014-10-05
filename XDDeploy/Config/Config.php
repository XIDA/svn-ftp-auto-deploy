<?php
	namespace XDDeploy\Config;
	use XDDeploy\Utils\Logger;

	/**
	 * 	Deploy Configuration
	 *
	 * 	@author XIDA
	 */
	class Config extends Base {

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
		 *	Single deploy config
		 *
		 *	@param	array		$data		Configuration array from file
		 *	@param	boolean		$preset		Is this a preset configuration?
		 *
		 *	@return \XDDeploy\Config\Config
		 */
		public function __construct($data, $preset = false) {
			parent::__construct($data, $preset);

			// create a new ftp configuration object, if the key exists
			if(!$this->isPreset() || $this->getValue('ftp')) {
				$this->ftp = new Ftp($this->getValue('ftp'));
			}

			// create a new svn configuration object, if the key exists
			if(!$this->isPreset()  || $this->getValue('svn')) {
				$this->svn = new Svn($this->getValue('svn'));
			}

			return $this;
		}

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			if(!$this->getName()) {
				Logger::configError("Property 'name' is required.");
			}
		}

		/**
		 *	Get current name
		 *
		 *	@return string
		 */
		public function getName() {
			return $this->getValue('name');
		}

		/**
		 *	Get version file name.
		 *	Default is 'deploy.ver'
		 *
		 *	@return string
		 */
		public function getVersionFile() {
			return $this->getValue('version_file') ?: 'deploy.ver';
		}

		/**
		 *	Debug mode
		 *
		 *	@return boolean
		 */
		public function isDebug() {
			return (boolean) $this->getValue('debug');
		}

		/**
		 *	Logging mode
		 *
		 *	@return boolean
		 */
		public function isVerbose() {
			return (boolean) $this->getValue('verbose');
		}

		/**
		 *	Execute after deploy commands
		 *
		 *	@return array
		 */
		public function getExecuteAfter() {
			return (array) $this->getValue('executeAfter');
		}

		/**
		 *	Execute before deploy commands
		 *
		 *	@return array
		 */
		public function getExecuteBefore() {
			return (array) $this->getValue('executeBefore');
		}
	}
?>