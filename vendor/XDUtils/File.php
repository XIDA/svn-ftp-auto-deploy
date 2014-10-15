<?php
	namespace XDUtils;

	/**
	 * 	File and Folder Utils
	 *
	 * 	@author XIDA
	 */
	class File {

		/**
		 *	Get all Files form a directory as \RecursiveDirectoryIterator
		 *	@see http://php.net/manual/de/class.recursivedirectoryiterator.php
		 *
		 *	@param	string			$path
		 *
		 *	@return \RecursiveIteratorIterator
		 */
		public static function getFilesRecursive($path) {
			if(!file_exists($path)) {
				return false;
			}
			return new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(
						$path,
						\FilesystemIterator::SKIP_DOTS
					),
				\RecursiveIteratorIterator::SELF_FIRST
			);
		}

		/**
		 *	Get a list of all directories in specified path (non-recursive!)
		 *
		 *	@param	string			$path		Path with ending slash
		 *
		 *	@return array
		 */
		public static function getDirectoryList($path) {
			$files = array();
			if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$files[] = self::getCleanedPath($path . DS . $file);
					}
				}
				closedir($handle);
			}
			return $files;
		}

		/**
		 *	Replace slashes / double slashes with the default separator.
		 *
		 *	@param	string			$path
		 *
		 *	@return string
		 */
		public static function getCleanedPath($path) {
			// replace slashes with default slash
			$string = str_replace(array('\\', '/'), '/', $path);
			// remove double slashes
			return preg_replace('~(^|[^:])//+~', '\\1/', $string);
		}
	}
?>