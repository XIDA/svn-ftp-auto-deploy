<?php
/***
 * DustinGraham.com Deploy Script
 *  - Checks ftp for current version
 *  - "Exports" changes from SVN
 *  - Uploads via ftp and updates version file with latest SVN version.
 *
 */

if(sizeOf($argv) < 2) {
	echo 'error: please add the config file as argument 1' . PHP_EOL;
	exit;
}

$configName = $argv[1];

$config = require('configs/config_' . $configName . '.php');

require('classes/svn.php');
require('classes/ftp.php');
require('classes/filesystem.php');
require('classes/Logger.php');


$fs = new FileSystem($config);
$svn = new Svn($fs, $config);
$ftp = new Ftp($fs, $config);

$rVer = $ftp->getCurrentVersion();
$svnLatestVer = $svn->getCurrentVersion();

if($rVer == "") {
	Logger::e('error: could not get version from FTP');
	exit;
}

if($rVer  == -1) {
	echo 'No ' . $config['version_file'] . ' file found on the ftp, is this your first commit? (type y to continue)' . PHP_EOL;
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(trim($line) != 'y'){
		echo "ABORTING!\n";
		exit;
	}
	echo "\n";
	$rVer = 0;
}

if(sizeOf($argv) > 2) {
	$sVer = $argv[2];
	if($sVer > $svnLatestVer) {
		Logger::e('target revison is greater than latest svn revision ' . $svnLatestVer);
		exit;
	}
} else {
	$sVer = $svn->getCurrentVersion();
}

Logger::i('ftp version: ' . $rVer);
Logger::i('svn target version: ' . $sVer);

if ($config['debug'])
{
    var_dump($rVer, $sVer, $config);
	exit;
}

if ($sVer != $rVer) {
	Logger::i('collecting changed files');
    $changes = $svn->checkoutChanges($sVer, $rVer);

    Logger::i('found ' . (count($changes['files'])) . ' files / directories that changed and ' . (count($changes['delFiles'])) . ' files to delete');

    // Create a .ver file
    $fs->addSvnVersion($sVer);

    $changes['files'][] = $config['version_file'];

    $ftp->putChanges($changes);

	Logger::i('done');

} else {
	Logger::i('Nothing to do - Up to date');
}

$fs->removeTempFolder();

