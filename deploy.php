<?php
	use XDUtils\Logger;

	error_reporting(E_ALL);

	// Absolute path to the root folder
	define('ROOT', realpath(__DIR__) . '/');

	require(ROOT . 'vendor/Loader.php');
	// add non namespaced classes
	new \Loader(
		array(
			'Zebra_Database' => '/stefangabos/zebra_database/Zebra_Database.php',
		)
	);

	$options = (getopt("c::v::"));
	$config	 = null;
	$version = null;

	// checking command line parameters
	if(sizeOf($options) > 0 && isset($options['c'])) {
		$config	 = $options['c'];
		if(isset($options['v'])) {
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

	Logger::setLogFileDir(ROOT . 'log');
	Logger::setLogInColors(true);
	var_dump($version);
	new \XDDeploy\Deploy($config, $version);
?>