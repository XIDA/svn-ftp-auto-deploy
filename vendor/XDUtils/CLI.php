<?php
	namespace XDUtils;

	/**
	 * 	Hepls with Command line interface
	 *
	 * 	@author XIDA
	 */
	class CLI {

		/**
		 *	Possible command line text colors
		 *
		 *	@var array
		 */
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

		/**
		 *	Possible command line text background colors
		 * 
		 *	@var array
		 */
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

		/**
		 *	Creates a colored string for command line with ANSI
		 *	@see http://softkube.com/blog/generating-command-line-colors-with-php
		 *
		 *	There are some problems with windows
		 *	@see http://softkube.com/blog/ansi-command-line-colors-under-windows
		 *
		 *	@param	string			$string
		 *	@param	string			$foreground_color
		 *	@param	string			$background_color
		 *
		 *	@return string
		 */
		public static function getColoredString($string, $foreground_color = null, $background_color = null) {
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


		/**
		 *	Wait for a user command line input and validates it
		 *
		 *	@param	array			$values				Allowed values as array
		 *
		 *	@return boolean			True if input is one of the values
		 */
		public static function userInput(array $values) {
			$input = trim(fgets(STDIN));
			if(in_array($input, $values)) {
				return $input;
			}
			return false;
		}
	}
?>