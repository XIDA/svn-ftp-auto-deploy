<?php
	namespace XDDeploy;
	use XDDeploy\Utils\Logger;

	// System Start Time
	define('START_TIME', microtime(true));

	// System Start Memory
	define('START_MEMORY_USAGE', memory_get_usage());

	// Extension of all PHP files
	define('EXT', '.php');

	// Directory separator (Unix-Style works on all OS)
	define('DS', '/');

	// Absolute path to the root folder
	define('ROOT', realpath(__DIR__) . DS);

	error_reporting(E_ALL);

	require(ROOT . 'XDDeploy/Loader.php');
	new Loader();

	$options = (getopt("c:v:"));
	$config	 = "";
	$version = "";

	// checking command line parameters
	if(sizeOf($options) > 0 && $options['c']) {
		$config	 = $options['c'];
		if($options['v']) {
			$version = $options['v'];
		}
	} else {
		if(isset($argv[1])) {
			$config = $argv[1];
		}
		if(isset($argv[2])) {
			$version = $argv[2];
		}
	}

	Logger::setLogDir(dirname(__FILE__) );
	new Deploy($config, $version);
?>