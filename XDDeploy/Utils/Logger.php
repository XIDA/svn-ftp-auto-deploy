<?php
	namespace XDDeploy\Utils;

	/**
	 * Description of Logger
	 *
	 * @author xida
	 */
	class Logger {

		public static $LOG_IN_COLOR = false;
		private static $logDir;

		public static function setLogDir($dir) {
			Logger::$logDir = $dir;
		}
		public static function abort($text = '') {
			if($text) {
				self::e('Exit: ' . $text);
			} else {
				self::e('Exit');
			}
			die();
		}

		public static function configError($error) {
			self::e('[Config Error] - ' . $error);
		}

		public static function configNote($note) {
			self::n('[Config Note] - ' . $note);
		}

		public static function configInfo($info) {
			self::n('[Config Info] - ' . $info);
		}

		public static function e($text = "", $noEndOfFile = false) {
			self::l(self::colorize($text, 'FAILURE'), $noEndOfFile);
		}

		public static function n($text = "", $noEndOfFile = false, $timeStamp = false) {
			self::l(self::colorize($text, 'NOTE'), $noEndOfFile, $timeStamp);
		}

		public static function i($text = "", $noEndOfFile = false) {
			self::l(self::colorize($text, 'SUCCESS'), $noEndOfFile);
		}

		public static function l($text = "", $noEndOfFile = false, $timeStamp = false) {
			$output = $text . ($noEndOfFile ? '' : PHP_EOL);
			if($timeStamp) {
				$output = date('H:i:s') . " " . $output;
			}
			echo $output;
		}

		public static function fileLog($text) {

			if (!file_exists(Logger::$logDir . '\logs')) {
				mkdir(Logger::$logDir . '\logs');
			}

			$file = Logger::$logDir . '\logs\log.txt';
			/* not needed, we can use FILE_APPEND flag
			$current = "";
			if(file_exists($file)) {
				$current = file_get_contents($file);
			}
			*/
			$text = date('d.m.Y H:i:s')  . " - " . $text . PHP_EOL;
			file_put_contents($file, $text, FILE_APPEND);
		}


		private static function colorize($text, $status) {
			if (!self::$LOG_IN_COLOR) {
				return $text;
			}

			$out = "";
			switch ($status) {
				case "SUCCESS":
					$out = "[42m"; //Green background
					break;
				case "FAILURE":
					$out = "[41m"; //Red background
					break;
				case "WARNING":
					$out = "[43m"; //Yellow background
					break;
				case "NOTE":
					$out = "[44m"; //Blue background
					break;
				default:
					throw new Exception("Invalid status: " . $status);
			}
			return chr(27) . "$out" . "$text" . chr(27) . "[0m";
		}

	}
?>