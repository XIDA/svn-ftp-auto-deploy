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

	$options	= (getopt("c:v:"));
	$config		= $options['c'] ?: $argv[1];
	$version	= ($options['v'] || $options['c']) ? $options['v'] : $argv[2];

	new Deploy($config, $version);
?>