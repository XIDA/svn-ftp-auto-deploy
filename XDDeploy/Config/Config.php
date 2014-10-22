<?php
	namespace XDDeploy\Config;
	use XDUtils\Logger;

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
		 *	Database Configuration
		 *	@var DB
		 */
		public $db;

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
			// if this is a normal config, a ftp config object is required
			if(!$this->isPreset() || $this->getValue('ftp')) {
				$this->ftp = new Ftp($this->getValue('ftp'), $preset);
			}

			// create a new svn configuration object, if the key exists
			// if this is a normal config, a svn config object is required
			if(!$this->isPreset() || $this->getValue('svn')) {
				$this->svn = new Svn($this->getValue('svn'), $preset);
			}

			// create a new database configuration object, if the key exists
			// if this is a normal config, a database config object is required
			if(!$this->isPreset() && $this->getValue('db')) {
				$this->db = new DB($this->getValue('db'), $preset);
			}

			return $this;
		}

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			$valid = true;
			if(!$this->getName()) {
				$valid = false;
				Logger::error("Property 'name' is required.");
			}
			return $valid;
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
			return (string) $this->getValue('version_file') ?: 'deploy.ver';
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
		 *	Test mode
		 *
		 *	@return boolean
		 */
		public function isTest() {
			return (boolean) $this->getValue('test');
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