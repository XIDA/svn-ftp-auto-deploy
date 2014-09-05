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


$fs = new FileSystem($config);
$svn = new Svn($fs, $config);
$ftp = new Ftp($fs, $config);

$rVer = $ftp->getCurrentVersion();
$svnLatestVer = $svn->getCurrentVersion();

if($rVer == "") {
	echo 'error: could not get version from FTP' . PHP_EOL;
	exit;
}

if($rVer  == -1) {
	echo 'No ' . $this->config['version_file'] . ' file found on the ftp, is this your fist commit? (type y to continue)\n';
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
		echo 'error: target revison is greater than latest svn revision ' . $svnLatestVer . PHP_EOL;
		exit;	
	}
} else {
	$sVer = $svn->getCurrentVersion();
}

echo 'ftp version: ' . $rVer . PHP_EOL;
echo 'svn target version: ' . $sVer . PHP_EOL;

if ($config['debug'])
{
    var_dump($rVer, $sVer, $config);
	exit;
}

if ($sVer != $rVer) {
	echo 'collecting changed files...' . PHP_EOL;
    $changes = $svn->checkoutChanges($sVer, $rVer);
    
    echo "Found " . (count($changes['files'])) . " files / directories that changed and " . (count($changes['delFiles'])) . " files to delete\r\n";
    
    // Create a .ver file
    $fs->addSvnVersion($sVer);
    
    $changes['files'][] = $config['version_file'];
	
    $ftp->putChanges($changes);
}
else
{
    echo "\r\n -= Up to date =-";
}

$fs->removeTempFolder();

