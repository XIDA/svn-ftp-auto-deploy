<?php
	namespace XDDeploy;

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

	require(ROOT . 'XDDeploy/Loader.php');
	new Loader();

	$options = (getopt("c:v:"));
	$config	 = "";
	$version = "";

	// checking command line parameters
	if(sizeOf($options) > 0) {

		if($options['c']) {
			$config	 = $options['c'];


			if($options['v']) {
				$version = $options['v'];
			}
		}

	} else {
		if(sizeOf($argv) > 1) {
			$config = $argv[1];
		}

		if(sizeOf($argv) > 2) {
			$version = $argv[2];
		}
	}

	new Deploy($config, $version);
?>