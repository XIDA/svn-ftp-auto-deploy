<?php
	namespace XDDeploy;
	use XDUtils\Logger;
	use XDUtils\CLI;
	use XDUtils\File;
	use XDTranslations\Translations;

	/**
	 * 	Deploy
	 *
	 * 	@author XIDA
	 */
	class Deploy {

		/**
		 *	Deploy for config
		 *
		 *	@param	string		$name			Name of the configuration
		 *	@param	int			$version		Version to deploy
		 */
		public function __construct($name = null, $version = null) {
			// setup translations
			Translations::setPath(dirname(__FILE__) . DS . 'resources');
			Logger::notice(Translations::get('welcome'));

			// check config file
			$configs	= Config\Manager::getConfigByName($name);
			echo Config\Manager::getLastConfigName();
			// setup log file name for deploy
			Logger::setLogFileName(date('Ymd') . '_' . Config\Manager::getLastConfigName() . '.txt');

			foreach($configs as $config) {
				Logger::info(Translations::get('deploy_start', array($config->getName())));
				$this->executeUrls($config->getExecuteBefore());
				$this->deploy($config, $version);
				$this->executeUrls($config->getExecuteAfter());
				Logger::info(Translations::get('deploy_end', array($config->getName())) . PHP_EOL . PHP_EOL);
			}
		}

		/**
		 *	Execute URLs
		 *
		 *	@param	array			$commands
		 */
		private function executeUrls($commands) {
			$urls = filter_var_array($commands, FILTER_VALIDATE_URL);
			foreach($urls as $url) {
				$result = file_get_contents($url);
				//L ogger::n($result);
			}
		}

		/**
		 *	Deploy to ftp server
		 *
		 *	@param	Config\Config	$config
		 *	@param	Ftp				$ftp
		 *
		 *	@return int
		 */
		private function checkFtpVersion(Config\Config $config, Ftp $ftp) {
			$ftpVer			= $ftp->getCurrentVersion();
			if(!isset($ftpVer) || $ftpVer === false) {
				Logger::userInput(Translations::get('version_ftp_not_found', array($config->getVersionFile())));
				if(CLI::userInput(array('y', 'yes', 1)) === false) {
					Logger::fatalError("ABORTING!");
				}
				$ftpVer = 0;
			}
			return $ftpVer;
		}

		/**
		 *	Get latest version from svn and checks if the user selected a version
		 *
		 *	@param	Config\Config	$config
		 *	@param	Svn 			$svn
		 *	@param	int/null			$version
		 *
		 *	@return int
		 */
		private function checkSvnVersion(Config\Config $config, Svn $svn, $version = null) {
			$svnLatestVer	= $svn->getCurrentVersion();
			if(isset($version)) {
				Logger::userInput(Translations::get('version_input', array($svnLatestVer, $config->getName())));
				// wait for the user to input the version
				$version	= CLI::userInput(range(1, $svnLatestVer));
				if($version === false) {
					Logger::fatalError(Translations::get('version_not_in_range', array($svnLatestVer)));
				}
				return $version;
			}
			return $svnLatestVer;
		}

		/**
		 *	Deploy to ftp server
		 *
		 *	@param	Config\Config	$config
		 *	@param	string			$version	Default: the newest version.
		 */
		private function deploy(Config\Config $config, $version = null) {
			$fs		= new FileSystem($config);
			$svn	= new Svn($fs, $config);
			$ftp	= new Ftp($fs, $config);

			// get the current active revisions
			$versionFrom	= $this->checkFtpVersion($config, $ftp);
			$versionTo		= $this->checkSvnVersion($config, $svn, $version);

			Logger::info(Translations::get('version_ftp', array($versionFrom)));
			Logger::info(Translations::get('version_svn', array($versionTo)));

			if ($versionTo != $versionFrom) {
				Logger::debug('collecting changed files..');
				$files = $svn->checkoutChanges($versionTo, $versionFrom);

				Logger::warning('found ' . (count($files['changed'])) . ' files / directories that changed and ' . (count($files['deleted'])) . ' files to delete');

				// Create a .ver file
				$fs->addSvnVersion($versionTo);

				// we should create a changes object
				// instead of creating such arrays
				// or upload the version file manually
				// via a new function $ftp->upload()
				$files['changed'][$config->getVersionFile()] = array(
					'path' => $config->getVersionFile(),
					'tempPath' => $fs->getTempFolder() . $config->getVersionFile(),
					'svnPath' => ''
				);

				$ftp->putChanges($files);
				Logger::success('Deploy done');
			} else {
				Logger::success('Nothing to do - Up to date');
			}

			// db deploy
			// @todo build a own class for db deploy related functions
			if(isset($config->db)) {
				$folder	= $svn->checkout($config->db->getRevisionFolder());
				$files	= File::getDirectoryList($folder . DS);

				$revisionFiles = array();
				foreach($files as $file) {
					// check if the file contains a revision
					preg_match('/r([\d,-]+)/', basename($file), $matches);
					if(isset($matches[1])				// file is valid if
						&& is_numeric($matches[1])		// contains a number
						&& $matches[1] > $versionFrom	// the number is greater than the current revision
						&& $matches[1] <= $versionTo	// and the number is smaller than the target revision
					) {
						$revisionFiles[] = $file;
					}
				}
				// there are sql files to deploy
				// so connect to database and do a backup
				if(sizeOf($revisionFiles) > 0) {
					// connect to db
					$db = new \XDUtils\Database();
					$db->connect(
						$config->db->getServer(),
						$config->db->getUser(),
						$config->db->getPassword(),
						$config->db->getName()
					);
					$db->set_charset();

					// backup current database
					Logger::notice('[DB] Start Backup');
					$db->backupDatabase(ROOT . DS . 'dbbackup' . DS . 'backup-' . $config->db->getName() . '-' . time() . '.sql');
					Logger::success('[DB] Backup done');

					// deploy new revisions to db
					foreach($revisionFiles as $sqlFile) {
						Logger::info('[DB] Deploy revision file: ' . $sqlFile);
						$db->parse_file($sqlFile);
						Logger::success('[DB] Deploy done');
					}
				}
			}

			// remove the temp folder
			$fs->removeTempFolder();
			return true;
		}
	}
?>