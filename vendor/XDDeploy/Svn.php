<?php
	namespace XDDeploy;
	use XDUtils\Logger;

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

		private function getFileNameWithoutSVNUrl($fileUrl) {
			return str_replace($this->config->svn->getRoot() . $this->config->svn->getSubfolder(), "", $fileUrl);
		}

		public function checkoutChanges($targetRev, $rVer) {
			$changes = $this->getRecentChanges($targetRev, $rVer);
			//e cho '...' . PHP_EOL;
			//v ar_dump($changes);

			Logger::info('exporting files from svn');

			foreach ($changes['files'] as $f) {
				//$path	 = $this->config->svn->getRoot() . $f;

				$file = $this->getFileNameWithoutSVNUrl($f);
				Logger::note('exporting ' . $file . '.. ');

				//$file = substr($f, strlen($this->config->svn->getSubfolder()) - 1);
				//e cho "file: " . $file . PHP_EOL;
				$target = $this->fs->getTempFolder() . $file;
				//e cho 'target: ' . $target . '<--' . PHP_EOL;
				//var_dump($target, $file, $f, $path); exit;
				// Ensure Directory Exists
				$this->fs->ensureFolderExists($target);

				$cmd = 'svn export ' . $this->loginString . '--force ' . $f . '@' . $targetRev . ' ' . $target;
				//e cho 'cmd: ' . $cmd . '<--' . PHP_EOL;
				Logger::note('..done');
				exec($cmd);
			}

			$svn_ver = $this->getSvnVersion();
			$this->fs->addSvnVersion($svn_ver);

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

		protected function getRecentChanges($sVer, $rVer) {
			$raw_log = $this->getChangeLog($sVer, $rVer);

			$changes = $this->getChangeArr($raw_log);

			return $changes;
		}

		public function getCurrentVersion() {
			return $this->getSvnVersion();
		}

		protected function getSvnVersion() {
			$cmd		= 'svn info --xml ' . $this->loginString . $this->config->svn->getRoot();
			$result		= shell_exec($cmd);
			$xml		= new \SimpleXMLElement($result, LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE);
			if(!$xml) {
				Logger::fatalError('SVN ERROR!');
			}
			return $xml->entry['revision'];
		}

		protected function getChangeLog($targetVer, $ftpVersion) {
			// We want the subfolder here because we only need to export
			// the files that should be uploaded.
			$repo = $this->config->svn->getRoot() . $this->config->svn->getSubfolder();

			//check if the file is there at all
			$cmd = 'svn log ' . $this->loginString . '-r 1:HEAD --limit 1 ' . $repo;


			$exec = exec($cmd, $out);

			// this happens if your desired path isn't in the svn anymore
			// for example if the svn subdir was deleted
			if (sizeOf($out) == 0) {
				Logger::error('there is a problem with the path you a trying to check out');
				Logger::error('use the following command to find out what\'s wrong');
				Logger::info($cmd);
				exit;
			}

			// we have to check if the svn path is available at the desired revision
			// we use svn log for that and the output looks like this
			// r41345 | bk | 2014-09-04 14:57:16 +0200 (Do, 04 Sep 2014) | 1 line\n\nmega changes\n
			// so after the r we will find the revision number, let's use regex
			$re						 = "/r([0-9]*) \\|/";
			preg_match($re, $out[1], $matches);
			// matches[1] contains the revision number
			$firstRevisionOfSVNPath	 = $matches[1];

			if ($firstRevisionOfSVNPath > $targetVer) {
				Logger::error('you are trying to update to revision ' . $targetVer . ' but the first revision of your svn path is ' . $firstRevisionOfSVNPath);
				Logger::error('use the following command to find out what\'s wrong');
				Logger::info($cmd);
				exit;
			}

			$cmd = 'svn diff ' . $this->loginString . $repo . ' --summarize -r ' . $ftpVersion . ':' . $targetVer;
			//e cho $cmd . PHP_EOL;

			$out	 = null;
			$return	 = null;
			$exec	 = exec($cmd, $out);

			return $out;
		}

		protected function getChangeArr($lines) {
			$delFiles	 = array();
			$files		 = array();

			$repo	 = $this->config->svn->getRoot() . $this->config->svn->getSubfolder();
			$repo	 = rtrim($repo, DS);

			$totLines = count($lines);
			for ($i = 0; $i < $totLines; $i++) {
				//e cho "\nInside FOR i = $i";
				$curLine = $lines[$i];
				//remove \r and \n
				$curLine = str_replace("\r", "", $curLine);
				$curLine = str_replace("\n", "", $curLine);

				$parts	 = explode(" ", $curLine);
				$sts	 = $parts[0];
				$file	 = $parts[7];
				$file	 = rtrim($file, DS);

				//if the file url is the same as the repo url, we remove it
				if ($file == $repo) {
					continue;
				}

				if ($this->isInIgnoreList($this->getFileNameWithoutSVNUrl($file))) {
					continue;
				}

				if ($sts == 'D') {
					$delFiles[] = $file;
				} else {
					$files[] = $file;
				}
			}
			//e cho "\r\n".'Completed SVN Parsing'."\r\n";

			$returnArray			 = array();
			$returnArray['files']	 = array_unique($files);
			$returnArray['delFiles'] = array_unique($delFiles);

			return $returnArray;
		}

	}