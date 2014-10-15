<?php
	namespace XDDeploy\Config;
	use XDUtils\File;
	use XDUtils\Logger;

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
			$valid = true;
			if(!$this->getPassword()) {
				$valid = false;
				Logger::error("Property 'ftp->password' is required.");
			}
			if(!$this->getServer()) {
				$valid = false;
				Logger::error("Property 'ftp->server' is required.");
			}
			if(!$this->getUser()) {
				$valid = false;
				Logger::error("Property 'ftp->user' is required.");
			}
			return $valid;
		}

		/**
		 *	Get FTP Root path
		 *
		 *	@return string
		 */
		public function getRoot() {
			return File::getCleanedPath(DS . $this->getValue('root') . DS);
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