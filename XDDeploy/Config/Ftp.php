<?php
	namespace XDDeploy\Config;
	use XDDeploy\Logger;

	/**
	 * 	FTP Configs
	 *
	 * 	@author XIDA
	 */
	class Ftp {
		/**
		 *	Store FTP Configurations
		 *	@var array
		 */
		private $config;

		/**
		 *	Setup FTP config
		 *
		 *	@param	array		$config
		 */
		public function __construct($config) {
			$this->config = $config;
			$this->validateConfig();
		}

		/**
		 *	Validate all required paramaters
		 */
		private function validateConfig() {
			if(!$this->getPassword()) {
				Logger::e('The FTP Password is not set!');
				die();
			} elseif(!$this->getServer()) {
				Logger::e('The FTP Server is not set!');
				die();
			} elseif(!$this->getUser()) {
				Logger::e('The FTP Username is not set!');
				die();
			}
		}

		/**
		 *	Get FTP Root path
		 *
		 *	@return string
		 */
		public function getRoot() {
			return $this->config['root'];
		}

		/**
		 *	Get FTP Username
		 *
		 *	@return string
		 */
		public function getUser() {
			return $this->config['username'];
		}

		/**
		 *	Get FTP Password
		 *
		 *	@return string
		 */
		public function getPassword() {
			return $this->config['password'];
		}

		/**
		 *	Get FTP Servername
		 *
		 *	@return string
		 */
		public function getServer() {
			return $this->config['server'];
		}
	}
?>