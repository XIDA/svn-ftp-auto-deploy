<?php
	namespace XDDeploy\Config;

	/**
	 *  SVN Configs
	 *
	 * 	@author XIDA
	 */
	class Svn extends Base {

		/**
		 *	Validate all required paramaters
		 */
		protected function validateConfig() {
			//
		}

		/**
		 *	Get SVN Root path
		 *
		 *	@return string
		 */
		public function getRoot() {
			return $this->getValue('root');
		}

		/**
		 *	Get SVN Subfolder
		 *
		 *	@return string
		 */
		public function getSubfolder() {
			return $this->getValue('subfolder');
		}

		/**
		 *	Get Files/Folders to ignore
		 *
		 *	@return array
		 */
		public function getIgnore() {
			return (array) $this->getValue('ignore');
		}

		/**
		 *	Get SVN Username
		 *
		 *	@return string
		 */
		public function getUser() {
			return $this->getValue('username');
		}

		/**
		 *	Get SVN Password
		 *
		 *	@return string
		 */
		public function getPassword() {
			return $this->getValue('password');
		}
	}
?>