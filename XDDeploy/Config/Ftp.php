<?php
	namespace XDDeploy\Config;
	use XDDeploy\Utils\Logger;

	/**
	 * 	FTP Configs
	 *
	 * 	@author XIDA
	 */
	class Ftp extends Base {

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			if(!$this->getPassword()) {
				Logger::configError("Property 'ftp->password' is required.");
			} elseif(!$this->getServer()) {
				Logger::configError("Property 'ftp->server' is required.");
			} elseif(!$this->getUser()) {
				Logger::configError("Property 'ftp->user' is required.");
			}
		}

		/**
		 *	Get FTP Root path
		 *
		 *	@return string
		 */
		public function getRoot() {
			return $this->getValue('root');
		}

		/**
		 *	Get FTP Username
		 *
		 *	@return string
		 */
		public function getUser() {
			return $this->getValue('username');
		}

		/**
		 *	Get FTP Password
		 *
		 *	@return string
		 */
		public function getPassword() {
			return $this->getValue('password');
		}

		/**
		 *	Get FTP Servername
		 *
		 *	@return string
		 */
		public function getServer() {
			return $this->getValue('server');
		}

		/**
		 *	Number of retries to upload a file via FTP
		 *	Default: 3
		 *
		 *	@return int
		 */
		public function getUploadRetries() {
			return (int) $this->getValue('uploadRetries') ?: 3;
		}
	}
?>