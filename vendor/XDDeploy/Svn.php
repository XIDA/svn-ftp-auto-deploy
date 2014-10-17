<?php
	namespace XDDeploy;
	use XDUtils\Logger;
	use XDUtils\File;

	class Svn {

		/**
		 *	@var FileSystem
		 */
		private $fs;

		/**
		 *	@var Config\Config
		 */
		private $config;
		private $loginString;

		public function __construct($fs, $config) {
			$this->fs		 = $fs;
			$this->config	 = $config;

			$this->loginString = "";
			if (strlen($this->config->svn->getUser()) > 0 && strlen($this->config->svn->getPassword()) > 0) {
				$this->loginString = '--username ' . $this->config->svn->getUser() . ' --password ' . $this->config->svn->getPassword() . ' ';
			}
		}

		private function getPathWithoutSVNUrl($fileUrl) {
			return str_replace($this->getRepositoryUrl(), "", $fileUrl);
		}

		private function getTempPathForFile($file) {
			return $this->fs->getTempFolder() . $this->getPathWithoutSVNUrl($file);
		}

		public function checkout($path) {
			Logger::notice('[SVN] export path: ' . $path);
			$cmd = 'svn export ' . $this->loginString . '--force ' . $this->getRepositoryUrl() . $path . ' ' . $this->fs->getTempFolder() . $path;
			exec($cmd);
			return $this->fs->getTempFolder() . $path;
		}

		public function checkoutChanges($targetRev, $rVer) {
			$changes = $this->getRecentChanges($targetRev, $rVer);
			//e cho '...' . PHP_EOL;
			//v ar_dump($changes);

			Logger::info('[SVN] exporting files');

			foreach ($changes['changed'] as $file) {
				//$path	 = $this->config->svn->getRoot() . $f;
				Logger::notice('[SVN] exporting ' . $file['path'] . '.. ');

				// Ensure Directory Exists
				File::createDirectoryForFile($file['tempPath']);

				$cmd = 'svn export ' . $this->loginString . '--force ' . $file['svnPath'] . '@' . $targetRev . ' ' . $file['tempPath'];
				//e cho 'cmd: ' . $cmd . '<--' . PHP_EOL;
				exec($cmd);
				Logger::notice('[SVN] exporting ' . $this->getPathWithoutSVNUrl($file['path']) . ' done');
			}

			// MM: svn version file will be added later in Deploy.php
			//$svn_ver = $this->getSvnVersion();
			//$this->fs->addSvnVersion($svn_ver);

			return $changes;
		}

		private function isInIgnoreList($filePath) {
			foreach ($this->config->svn->getIgnore() as $cIgnore) {
				//e cho $filePath . ' --- ' . $cIgnore . PHP_EOL;
				$pos = strpos(strtolower($filePath), strtolower($cIgnore));
				//e cho $pos . PHP_EOL . PHP_EOL;
				if ($pos !== false) {
					Logger::info('Ignoring: ' . $filePath);
					return true;
				}
			}
			return false;
		}

		private function getRecentChanges($sVer, $rVer) {
			$xml = $this->getChangeLog($sVer, $rVer);
			return $this->getChangeArr($xml);
		}

		public function getRepositoryUrl() {
			// although each of this variables is cleaned by itself already
			// we need to clean the combination of the 2 again because if the subfolder is empty in the config
			// then this will result in 2 slashes
			return File::getCleanedPath($this->config->svn->getRoot() . $this->config->svn->getSubfolder());
		}

		public function getCurrentVersion() {
			return $this->getSvnVersion();
		}

		protected function getSvnVersion() {
			$cmd		= 'svn info --xml ' . $this->loginString . $this->config->svn->getRoot();
			$xml		= $this->getXMLForCommand($cmd);
			return (int) $xml->entry['revision'];
		}

		private function getXMLForCommand($cmd) {
			$result		= shell_exec($cmd);
			// @todo error handling
			// output can be something like 'svn: EXXXX'
			//p rint_r($result);
			$xml		= new \SimpleXMLElement($result, LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE);
			if(!$xml) {
				Logger::fatalError('SVN ERROR! Command: ' . $cmd);
			}
			return $xml;
		}



		private function getChangeLog($targetVer, $ftpVersion) {
			//check if the file is there at all
			$cmd = 'svn log --xml ' . $this->loginString . '-r 1:HEAD --limit 1 ' . $this->getRepositoryUrl();
			$xml = $this->getXMLForCommand($cmd);
			$availableRevision = (int) $xml->logentry['revision'];

			if ($availableRevision > $targetVer) {
				Logger::fatalError('you are trying to update to revision ' . $targetVer . ' but the first revision of your svn path is ' . $availableRevision);
			}

			// get diff between versions
			$cmd = 'svn diff --xml ' . $this->loginString . $this->getRepositoryUrl() . ' --summarize -r ' . $ftpVersion . ':' . $targetVer;
			return $this->getXMLForCommand($cmd);
		}

		/**
		 *	Get changes from svn output xml
		 *
		 *	@param	\SimpleXMLElement $xml
		 *
		 *	XML Example
		 *	<diff>
		 *		<paths>
		 *			<path item="deleted" props="none" kind="file">
		 *				https://url.to/repository/folder/file.example
		 *			</path>
		 *			..
		 *		</paths>
		 *	</diff>
		 *
		 *	@return array
		 */
		private function getChangeArr(\SimpleXMLElement $xml) {
			$files		 = array(
				'deleted'	=> array(),
				'changed'	=> array(),
				'other'		=> array()
			);

			foreach($xml->paths->path as $file) {
				// extract file path
				$svnPath	= $file->__toString();
				$path		= $this->getPathWithoutSVNUrl($svnPath);
				$tempPath	= $this->getTempPathForFile($path);

				// get the current type
				$type = (string) $file['item'];

				// if path is in ignore list, or the repository itself ignore them
				if($this->isInIgnoreList($path)
					|| $file == rtrim($this->getRepositoryUrl(), DS)) {
					continue;
				}

				// file info to reduce replaces in other classe
				$fileInfo = array(
					'tempPath'	=> $tempPath,
					'path'		=> $path,
					'svnPath'	=> $svnPath
				);

				switch($type) {
					case 'modified':
					case 'added':		// file to upload
						// set array key to have every file only once
						$files['changed'][$path] = $fileInfo;
					break;
					case 'deleted':		// file to delete
						// set array key to have every file only once
						$files['deleted'][$path] = $fileInfo;
					break;
					default:			// files where the svn properties changed
						$files['other'][$path] = $fileInfo;
					break;
				}
			}
			return $files;
		}
	}
?>