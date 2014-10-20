<?php
	namespace XDUtils;

	/**
	 *	Manages logs
	 *
	 *	@author xida
	 */
	class Logger {

		/**
		 *	Stores if log to file is enabled
		 *
		 *	@var boolean
		 */
		private static $logToFile = true;

		/**
		 *	Stores if debug logs are enabled
		 *
		 *	@var boolean
		 */
		private static $logDebug = false;

		/**
		 *	Stores if logging to command line is enabled
		 *
		 *	@var boolean
		 */
		private static $logToCli = false;

		/**
		 *	Stores if colorize of logs is enabled
		 *
		 *	@var boolean
		 */
		private static $logInColors = false;

		/**
		 *	The log dir path
		 *
		 *	@var string
		 */
		private static $logFileDir = 'log';

		/**
		 *	The log file name
		 *
		 *	@var string
		 */
		private static $logFileName = 'log.txt';

		/**
		 *	Enable/Disable file log
		 *
		 *	@param	boolean		$value
		 */
		public static function setLogToFile($value) {
			self::$logToFile = (boolean) $value;
		}

		/**
		 *	Enable/Disable log to command line
		 *
		 *	@param	boolean		$value
		 */
		public static function setLogToCli($value) {
			self::$logToCli = (boolean) $value;
		}

		/**
		 *	Enable/Disable debug logs
		 *
		 *	@param	boolean		$value
		 */
		public static function setLogDebug($value) {
			self::$logDebug = (boolean) $value;
		}

		/**
		 *	Enable/Disable colorize of logs on command line
		 *
		 *	@param	boolean		$value
		 */
		public static function setLogInColors($value) {
			self::$logInColors = (boolean) $value;
		}

		/**
		 *	Set the log file dir
		 *
		 *	@param	string		$dir
		 */
		public static function setLogFileDir($dir) {
			Logger::$logFileDir = $dir;
		}

		/**
		 *	Set the log file dir
		 *
		 *	@param	string		$name
		 */
		public static function setLogFileName($name) {
			if(is_string($name)) {
				Logger::$logFileName = $name;
			}
		}



		/**
		 *	Logs a fatal error and exit application
		 *
		 *	@param	string		$t
		 */
		public static function fatalError($t = '') {
			self::error('Exit' . ($t ? (': ' . $t) : ''));
			die();
		}

		/**
		 *	Logs a error
		 *
		 *	@param	string		$t
		 */
		public static function error($t = '') {
			self::important($t, 'red');
		}

		/**
		 *	Logs a warning
		 *
		 *	@param	string		$t
		 */
		public static function warning($t = '') {
			self::important($t, 'yellow');
		}

		/**
		 *	Logs a info
		 *
		 *	@param	string		$t
		 */
		public static function info($t = '') {
			self::important($t, 'white');
		}

		/**
		 *	Logs a notice
		 *
		 *	@param	string		$t
		 */
		public static function notice($t = '') {
			self::l($t, 'light_gray');
		}

		/**
		 *	Logs a debug message
		 *
		 *	@param	string		$t
		 */
		public static function debug($t = '') {
			if(self::$logDebug) {
				self::l('[DEBUG] ' . $t, 'light_gray');
			}
		}



		/**
		 *	Logs a success info
		 *
		 *	@param	string		$t
		 */
		public static function success($t = '') {
			self::l($t, 'green');
		}

		/**
		 *	Displays a user input message
		 *
		 *	@param	string		$t
		 */
		public static function userInput($t = '', $color = 'cyan') {
			self::l($t, $color);
		}

		/**
		 *	Logs a important message
		 *
		 *	@param	string		$t
		 */
		public static function important($t = '', $color = 'white') {
			self::l($t, $color, true);
		}

		/**
		 *	Logs text
		 *
		 *	@param	string		$text			Text to log
		 *	@param	string		$color			Colorize log on cli
		 *	@param	boolean		$fileLog		Log the message to file too
		 *	@param	string		$timeStamp		Display timestamp in log
		 */
		private static function l($text = '', $color = 'white', $fileLog = false, $timeStamp = true) {
			if(self::$logToFile && $fileLog) {
				self::fileLog($text);
			}

			if(self::$logToCli) {
				$log = '';
				if($timeStamp) {
					$log .= date('H:i:s') . ' - ';
				}
				if(self::$logInColors) {
					$log .= CLI::getColoredString($text, $color);
				} else {
					$log .= $text;
				}
				echo $log . PHP_EOL;
			}
		}

		/**
		 *	Logs to file
		 *
		 *	@param	string		$text
		 */
		public static function fileLog($text) {
			// creat dir if not exists
			if (!file_exists(self::$logFileDir)) {
				@mkdir(self::$logFileDir, 0777, true);
			}

			// build filename
			$file = File::getCleanedPath(self::$logFileDir . DS . self::$logFileName);

			// add timestamp
			$text = date('H:i:s') . " " . $text . PHP_EOL;

			// save log to file
			file_put_contents($file, $text, FILE_APPEND);
		}
	}
?>