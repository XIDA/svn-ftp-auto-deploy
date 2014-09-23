<?php
	namespace XDDeploy\Config;

	/**
	 *  SVN Configs
	 *
	 * 	@author XIDA
	 */
	class Svn {
		/**
		 *	Store svn config
		 *	@var array
		 */
		private $config;

		/**
		 *	Setup Config
		 *
		 *	@param array		$config
		 */
		public function __construct($config) {
			$this->config = $config;
			$this->validateConfig();
		}

		/**
		 *	Validate all required paramaters
		 */
		private function validateConfig() {
			//
		}

		/**
		 *	Get SVN Root path
		 *
		 *	@return string
		 */
		public function getRoot() {
			return $this->config['root'];
		}

		/**
		 *	Get SVN Subfolder
		 *
		 *	@return string
		 */
		public function getSubfolder() {
			return $this->config['subfolder'];
		}

		/**
		 *	Get Files/Folders to ignore
		 *
		 *	@return array
		 */
		public function getIgnore() {
			return (array) $this->config['ignore'];
		}

		/**
		 *	Get SVN Username
		 *
		 *	@return string
		 */
		public function getUser() {
			return $this->config['username'];
		}

		/**
		 *	Get SVN Password
		 *
		 *	@return string
		 */
		public function getPassword() {
			return $this->config['password'];
		}
	}
?>