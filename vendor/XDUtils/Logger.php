<?php
	namespace XDUtils;

	/**
	 *	Description of Logger
	 *
	 *	@author xida
	 */
	class Logger {

		public static $FILE_LOG = true;
		public static $LOG_IN_COLOR = false;
		private static $logDir;


		private static $TEXT_COLORS = array(
			'black'         => '0;30',
			'dark_gray'     => '1;30',
			'blue'          => '0;34',
			'light_blue'    => '1;34',
			'green'         => '0;32',
			'light_green'   => '1;32',
			'cyan'          => '0;36',
			'light_cyan'    => '1;36',
			'red'           => '0;31',
			'light_red'     => '1;31',
			'purple'        => '0;35',
			'light_purple'  => '1;35',
			'brown'         => '0;33',
			'yellow'        => '1;33',
			'light_gray'    => '0;37',
			'white'         => '1;37',
			'black_u'       => '4;30',   // underlined
			'red_u'         => '4;31',
			'green_u'       => '4;32',
			'yellow_u'      => '4;33',
			'blue_u'        => '4;34',
			'purple_u'      => '4;35',
			'cyan_u'        => '4;36',
			'white_u'       => '4;37'
		);

		private static $BACKGROUND_COLORS = array(
			'black'         => '40',
			'red'           => '41',
			'green'         => '42',
			'yellow'        => '43',
			'blue'          => '44',
			'magenta'       => '45',
			'cyan'          => '46',
			'light_gray'    => '47'
		);

		public static function setLogDir($dir) {
			Logger::$logDir = $dir;
		}

		public static function fatalError($t = '') {
			self::error('Exit' . ($t ? (': ' . $t) : ''));
			die();
		}

		public static function configError($error) {
			self::error('[Config Error] - ' . $error);
		}

		public static function configNote($note) {
			self::note('[Config Note] - ' . $note);
		}

		public static function configInfo($info) {
			self::info('[Config Info] - ' . $info);
		}

		public static function error($t = '') {
			self::l($t, 'red');
		}

		public static function info($t = '') {
			self::l($t, 'white');
		}

		public static function note($t = '') {
			self::l($t, 'light_gray');
		}

		public static function success($t = '') {
			self::l($t, 'green');
		}

		public static function warning($t = "") {
			self::l($t, 'yellow');
		}

		private static function l($text = "", $color = 'white', $timeStamp = true) {
			self::fileLog($text);
			echo ($timeStamp ? date('H:i:s') . ' - ' : '') . self::getColoredString($text, $color) . PHP_EOL;
		}

		public static function fileLog($text) {
			if (!file_exists(Logger::$logDir . '\logs')) {
				@mkdir(Logger::$logDir . '\logs', 0777, true);
			}
			echo 'hi';
			$file = Logger::$logDir . '\logs\log.txt';
			$text = date('d.m.Y H:i:s') . " - " . $text . PHP_EOL;
			file_put_contents($file, $text, FILE_APPEND);
		}


		private static function getColoredString($string, $foreground_color = null, $background_color = null) {
			if (!self::$LOG_IN_COLOR) {
				return $string;
			}

			$colored_string = "";

			// Check if given foreground color found
			if (isset(self::$TEXT_COLORS[$foreground_color])) {
				$colored_string .= "\033[" . self::$TEXT_COLORS[$foreground_color] . "m";
			}
			// Check if given background color found
			if (isset(self::$BACKGROUND_COLORS[$background_color])) {
				$colored_string .= "\033[" . self::$BACKGROUND_COLORS[$background_color] . "m";
			}

			// Add string and end coloring
			$colored_string .=  $string . "\033[0m";

			return $colored_string;
		}
	}
?>