<?php
	namespace XDDeploy\Config;
	use XDUtils\Logger;

	/**
	 * 	Database Configs
	 *
	 * 	@author XIDA
	 */
	class DB extends Base {

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			$valid = true;
			if($this->getPassword() === null) {
				$valid = false;
				Logger::error("Property 'db->password' is required.");
			}
			if(!$this->getServer()) {
				$valid = false;
				Logger::error("Property 'db->server' is required.");
			}
			if(!$this->getUser()) {
				$valid = false;
				Logger::error("Property 'db->user' is required.");
			}
			if(!$this->getName()) {
				$valid = false;
				Logger::error("Property 'db->name' is required.");
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

		/**
		 *	Folder name containing the .sql revision files
		 *
		 *	@return string
		 */
		public function getRevisionFolder() {
			return (string) $this->getValue('revision_folder') ?: 'dbrevisions';
		}
	}
?>