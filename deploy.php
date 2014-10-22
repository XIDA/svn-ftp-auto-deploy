<?php
	use XDUtils\Logger;
	use GetOptionKit\OptionCollection;
	use GetOptionKit\OptionParser;

	error_reporting(E_ALL);

	// Absolute path to the root folder
	define('ROOT', realpath(__DIR__) . '/');

	require(ROOT . 'Loader.php');
	// add non namespaced classes
	new \XD\Loader(
		array(
			'Zebra_Database' => 'vendor/stefangabos/zebra_database/Zebra_Database.php',
		),
		array(
			'GetOptionKit' => 'vendor/c9s/GetOptionKit/src/',
			'XDUtils' => 'vendor/xd'
		)
	);

	// configure logger
	Logger::setLogFileDir(ROOT . 'log');
	Logger::setLogInColors(true);
	Logger::setLogToCli(true);

	// setup CLI options
	$specs = new OptionCollection();
	$specs->add('c|config:', 'Config name. If not specified the application will give you a list to choose a config name from.')->isa('String');
	$specs->add('v|version', 'Version. If specified the application will ask you for the version number on each deploy.')->isa('Boolean');
	$specs->add('t|test', 'Enable test mode. No FTP File/DB will be changed.')->isa('Boolean');
	$specs->add('d|debug', 'Enable debug mode.');

	$specs->add('h|help', 'Show help');
	$parser		= new OptionParser($specs);
	$printer	= new GetOptionKit\OptionPrinter\ConsoleOptionPrinter;

	try {
		// try to parse the options..
	    $result		= $parser->parse($argv)->toArray();

		// show debug logs
		if(isset($result['debug'])) {
			Logger::setLogDebug(true);
		}

		// show command line help
		if(isset($result['help'])) {
			die($printer->render($specs));
		}

		// did the user specify a config name ?
		$config		= isset($result['config']) ? $result['config'] : null;

		// did the user specify the version parameter
		$version	= key_exists('version', $result) ? true : null;

		// start deploy!
		new \XDDeploy\Deploy($config, $version, isset($result['debug']), isset($result['test']));
	} catch( Exception $e ) {
	    echo $e->getMessage() . PHP_EOL;
		die($printer->render($specs));
	}
?>