<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logger
 *
 * @author xida
 */
class Logger {

	public static function e($text = "", $noEndOfFile = false) {
		$string = self::colorize($text, 'FAILURE');
		if(!$noEndOfFile) {
			$string .= PHP_EOL;
		}
		echo $string;
	}

	public static function n($text = "", $noEndOfFile = false) {
		$string = self::colorize($text, 'NOTE');
		if(!$noEndOfFile) {
			$string .= PHP_EOL;
		}
		echo $string;
	}

	public static function i($text = "", $noEndOfFile = false) {
		$string = self::colorize($text, 'SUCCESS');
		if(!$noEndOfFile) {
			$string .= PHP_EOL;
		}
		echo $string;
	}

	private static function colorize($text, $status) {
		$out = "";
		switch($status) {
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

