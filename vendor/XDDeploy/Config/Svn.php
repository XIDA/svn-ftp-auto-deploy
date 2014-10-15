<?php
	namespace XDDeploy\Config;
	use XDUtils\File;

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
			return true;
		}

		/**
		 *	Get SVN Root path
		 *
		 *	@return string
		 */
		public function getRoot() {
			return File::getCleanedPath($this->getValue('root') . DS);
		}

		/**
		 *	Get SVN Subfolder
		 *
		 *	@return string
		 */
		public function getSubfolder() {
			return File::getCleanedPath($this->getValue('subfolder') . DS);
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