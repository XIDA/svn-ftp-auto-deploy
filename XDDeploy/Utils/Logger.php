<?php
	namespace XDDeploy\Utils;

	/**
	 * Description of Logger
	 *
	 * @author xida
	 */
	class Logger {

		public static $LOG_IN_COLOR = false;

		public static function abort($text = '') {
			self::e('Exit: ' . $text);
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

		public static function n($text = "", $noEndOfFile = false) {
			self::l(self::colorize($text, 'NOTE'), $noEndOfFile);
		}

		public static function i($text = "", $noEndOfFile = false) {
			self::l(self::colorize($text, 'SUCCESS'), $noEndOfFile);
		}

		public static function l($text = "", $noEndOfFile = false) {
			echo date('H:i:s') . " " . $text . ($noEndOfFile ? '' : PHP_EOL);
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