<?php
	namespace XDDeploy;
	use XDUtils\File;

	class FileSystem {

		private $_temp;

		/**
		 * 	Configuration
		 *
		 * 	@var \Config\Config
		 */
		private $config;

		public function __construct($config) {
			$this->config = $config;
		}

		public function addSvnVersion($ver) {
			// Add "version_file" version file to "changes" to be uploaded
			$ver_file = $this->getTempFolder() . $this->config->getVersionFile();
			File::createDirectoryForFile($ver_file);
			file_put_contents($ver_file, $ver);
		}

		public function getTempFolder() {
			if (empty($this->_temp)) {
				$this->setupTempFolder();
			}

			return $this->_temp;
		}

		protected function setupTempFolder() {
			$tmp	 = realpath(getenv('TEMP'));
			$dirname = $tmp . DS . uniqid('deploy-');
			mkdir($dirname);

			$this->_temp = $dirname . DS;
		}

		public function removeTempFolder() {
			if (!empty($this->_temp)) {
				File::removeDirectoryRecursive($this->_temp);
			}
		}
	}
?>