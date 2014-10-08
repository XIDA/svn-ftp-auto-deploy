<?php
	namespace XDDeploy;
	use XDDeploy\Utils\Logger;

	class Ftp {

		/**
		 * @var FileSystem
		 */
		private $fs;

		/**
		 * @var Config\Config
		 */
		private $config;

		public function __construct($fs, $config) {
			$this->fs		 = $fs;
			$this->config	 = $config;
		}

		protected function log($msg) {
			if ($this->config->isVerbose()) {
				Logger::n("[FTP] " . $msg);
			}
		}

		public function getCurrentVersion() {
			$conn_id = $this->ftpGetConnection();

			$this->log('Connected:' . $conn_id);

			$this->ftpGoDir($conn_id, $this->config->ftp->getRoot());

			$this->log('Went to:' . $this->config->ftp->getRoot());

			$temp = $this->fs->getTempFolder();

			$this->log('Temp is: ' . $temp);

			$this->log('PWD is: ' . ftp_pwd($conn_id));

			$filepath = $this->config->getVersionFile();

			$this->log('Attempt GET: ' . $filepath);


			$success = @ftp_get($conn_id, $temp . $this->config->getVersionFile(), $filepath, FTP_BINARY);

			if (!$success) {
				$this->log('GET Failed');
				return "-1";
			}

			$this->log('GET Success');

			$data = file_get_contents($temp . $this->config->getVersionFile());

			$this->log('FTP Version: ' . $data);

			return $data;
		}

		private function getSourceForFile($change) {
			$source	 = str_replace($this->config->svn->getRoot() . $this->config->svn->getSubfolder(), "", $change);
			$source	 = $this->fs->getTempFolder() . str_replace('/', '\\', $source);
			$source	 = str_replace('\\', '\\\\', $source);
			return $source;
		}

		private function getDestinationForFile($change) {
			return $this->config->ftp->getRoot() . str_replace($this->config->svn->getRoot() . $this->config->svn->getSubfolder(), "", $change);
		}

		public function putChanges($changes) {
			$conn_id = $this->ftpGetConnection();
			//e cho ftp_pwd($conn_id);exit;

			foreach ($changes['files'] as $change) {
				// We want to strip the svn's subfolder from the change.
				// because that subfolder is exported to the $temp folder.
				$source		 = $this->getSourceForFile($change);
				//e cho 'source: ' . $source . PHP_EOL;
				// The ftp destination directory.
				$destination = $this->getDestinationForFile($change);
				//e cho 'destination: ' . $destination . PHP_EOL;

				if (is_dir($source)) {
					$this->ftpGoDir($conn_id, $destination);
				} else {
					$this->ftpGoDir($conn_id, dirname($destination));
				}

				//e cho 'source: ' . $source . PHP_EOL;
				if (is_dir($source)) {
					Logger::n('created directory ' . $destination);
					continue;
				}

				// upload the file
				$this->log('Source: ' . $source);
				$this->log('Destination: ' . $source);

				$i = 0;
				// retry uploading..
				while($i <= $this->config->ftp->getUploadRetries()) {
					$i++;
					Logger::n('uploading ' . $destination . ' ... ', true, true);
					$upload = ftp_put($conn_id, basename($destination), $source, FTP_BINARY);

					// check upload status
					if (!$upload) {
						Logger::n('FTP upload has failed! ( ' . $upload . ' ) Try: ' . $i);
						// reconnect and try to upload again
						$conn_id = $this->ftpGetConnection();
						continue;
					} else {
						//e cho "Uploaded $source to $destination <br />";
						Logger::n('done', false, true);
						break;
					}
				}
			}

			if ($changes['delFiles']) {
				foreach ($changes['delFiles'] as $change) {
					$destination = $this->getDestinationForFile($change);
					Logger::n('deleting ' . $destination);

					$source = $this->getSourceForFile($change);

					$this->ftpRecursiveDelete($conn_id, $destination);
				}
			}

			// close the FTP stream
			ftp_close($conn_id);
		}

		protected function ftpGoDir($conn_id, $dir) {
			$parts = explode('/', ltrim($dir, '/'));

			$current = '/';
			ftp_chdir($conn_id, $current);

			foreach ($parts as $part) {
				//e cho 'part: ' . $part . PHP_EOL;
				$current .= $part . '/';
				// Try to navigate
				if (@ftp_chdir($conn_id, $current)) {
					continue;
				}

				// Doesn't exist, make it.
				// without this an empty directory \ will be created
				if ($part == "\\") {
					continue;
				}
				ftp_mkdir($conn_id, $current);
				ftp_chdir($conn_id, $current);
			}
		}

		function hasSuffix($filename, $suffix) {
			$extLength	 = 4;
			$strpos		 = strpos($filename, $suffix);

			if ($strpos == (strlen($filename) - strlen($suffix) - $extLength)) {
				return true;
			}

			return false;
		}

		private function ftpRecursiveDelete($conn_id, $directory) {
			//if(ftp_size($conn_id, $directory) == -1) { return; }

			$targetPath = $directory;

			// deleting only works with absolute urls
			$strpos = strpos($directory,'/');
			if (!($strpos !== false && $strpos == 0)) {
				$targetPath = "/" . $directory;
			}

			# here we attempt to delete the file/directory
			if (!( @ftp_rmdir($conn_id, $targetPath) || @ftp_delete($conn_id, $targetPath) )) {
				# if the attempt to delete fails, get the file listing
				$filelist = @ftp_nlist($conn_id, $targetPath);
				//var_dump($filelist);exit;
				# loop through the file list and recursively delete the FILE in the list
				if ($filelist) {
					foreach ($filelist as $file) {

						$parts		 = explode("/", $file);
						$fileName	 = $parts[sizeOf($parts) - 1];
						if ($fileName == '.' || $fileName == '..') {
							continue;
						}

						//e cho 'filename: ' . $parts[sizeOf($parts) - 1] . '<--';
						//e cho 'FILE: ' . $file . '<--' . PHP_EOL;
						if ($file == ftp_pwd($conn_id)) {
							//e cho 'file is current dir... continue' . PHP_EOL;
							continue;
						}
						if (@ftp_chdir($conn_id, $file)) {
							// ok it's a dir
							// maybe it's empty?
							//e cho 'trying to remove dir' . PHP_EOL;
							if (@ftp_rmdir($conn_id, $file)) {

							} else {
								//e cho 'no success, recurse...' . PHP_EOL;
								//not empty recurse
								$this->ftpRecursiveDelete($conn_id, $file);
							}
						} else {
							// must be a file
							//e cho 'must be a file...' . PHP_EOL;
							@ftp_delete($conn_id, $file);
						}
					}
					$this->ftpRecursiveDelete($conn_id, $targetPath);
				}
			}
		}

		protected function ftpGetConnection() {
			$ftp_server	 = $this->config->ftp->getServer();
			$ftp_user	 = $this->config->ftp->getUser();
			$ftp_pass	 = $this->config->ftp->getPassword();

			// set up basic connection
			$conn_id = ftp_connect($ftp_server);
			if($conn_id === false) {
				$this->connectionFailed();
			}

			// login with username and password
			$login_result = ftp_login($conn_id, $ftp_user, $ftp_pass);

			// check connection
			if ($login_result === false) {
				$this->connectionFailed();
			} else {
				//e cho 'Connected to [[ '.$ftp_server . ' ]] for user [[ '.$ftp_user.' ]]'."\r\n";
			}

			// Passive Connection
			ftp_pasv($conn_id, true);

			return $conn_id;
		}

		private function connectionFailed() {
			Logger::e('FTP connection has failed!');
			Logger::e('Attempted to connect to ' . $this->config->ftp->getServer() . ' for user ' . $this->config->ftp->getUser());
			die();
		}

	}