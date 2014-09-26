<?php
	namespace XDDeploy\Utils;

	/**
	 * 	File and Folder Helper
	 *
	 * 	@author XIDA
	 */
	class File {

		/**
		 *	Get all Files form a directory as \RecursiveDirectoryIterator
		 *	@see http://php.net/manual/de/class.recursivedirectoryiterator.php
		 *
		 *	@param	string		$path
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
	}
?>