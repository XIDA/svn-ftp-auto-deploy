<?php
	namespace XDDeploy\Config;
	use XDUtils\Logger;

	/**
	 * 	FTP Configs
	 *
	 * 	@author XIDA
	 */
	class DB extends Base {

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			$valid = true;
			if(!$this->getPassword()) {
				$valid = false;
				Logger::configError("Property 'ftp->password' is required.");
			}
			if(!$this->getServer()) {
				$valid = false;
				Logger::configError("Property 'ftp->server' is required.");
			}
			if(!$this->getUser()) {
				$valid = false;
				Logger::configError("Property 'ftp->user' is required.");
			}
			if(!$this->getName()) {
				$valid = false;
				Logger::configError("Property 'ftp->user' is required.");
			}
			return $valid;
		}

		/**
		 *	Get Database name
		 *
		 *	@return string
		 */
		public function getName() {
			return $this->getValue('name');
		}

		/**
		 *	Get Database Username
		 *
		 *	@return string
		 */
		public function getUser() {
			return $this->getValue('username');
		}

		/**
		 *	Get Database Password
		 *
		 *	@return string
		 */
		public function getPassword() {
			return $this->getValue('password');
		}

		/**
		 *	Get Database Servername
		 *
		 *	@return string
		 */
		public function getServer() {
			return $this->getValue('server');
		}
	}
?>