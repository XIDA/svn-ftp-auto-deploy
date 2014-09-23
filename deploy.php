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

$config = new Config\Config($argv[1]);

$fs = new FileSystem($config);
$svn = new Svn($fs, $config);
$ftp = new Ftp($fs, $config);

$ftpVer = $ftp->getCurrentVersion();
$svnLatestVer = $svn->getCurrentVersion();

if($ftpVer == "") {
	Logger::e('error: could not get version from FTP');
	exit;
}

if($ftpVer  == -1) {
	echo 'No ' . $config->getVersionFile() . ' file found on the ftp, is this your first commit? (type y to continue)' . PHP_EOL;
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(trim($line) != 'y'){
		echo "ABORTING!\n";
		exit;
	}
	echo PHP_EOL;
	$ftpVer = 0;
}

if(sizeOf($argv) > 2) {
	$targetVer = $argv[2];
	if($targetVer > $svnLatestVer) {
		Logger::e('target revison is greater than latest svn revision ' . $svnLatestVer);
		exit;
	}
} else {
	$targetVer = $svn->getCurrentVersion();
}

Logger::i('ftp version: ' . $ftpVer);
Logger::i('svn target version: ' . $targetVer);

if ($config->isDebug()) {
    var_dump($ftpVer, $targetVer, $config);
	exit;
}

if ($targetVer != $ftpVer) {
	Logger::i('collecting changed files');
    $changes = $svn->checkoutChanges($targetVer, $ftpVer);

    Logger::i('found ' . (count($changes['files'])) . ' files / directories that changed and ' . (count($changes['delFiles'])) . ' files to delete');

    // Create a .ver file
	$fs->addSvnVersion($targetVer);

    $changes['files'][] = $config->getVersionFile();

    $ftp->putChanges($changes);
	Logger::i('done');
} else {
	Logger::i('Nothing to do - Up to date');
}

$fs->removeTempFolder();

