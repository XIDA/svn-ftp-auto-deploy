<?php
/***
 * DustinGraham.com Deploy Script
 *  - Checks ftp for current version
 *  - "Exports" changes from SVN
 *  - Uploads via ftp and updates version file with latest SVN version.
 * 
 */

$config = require('config.php');

require('classes/svn.php');
require('classes/ftp.php');
require('classes/filesystem.php');


$fs = new FileSystem($config);
$svn = new Svn($fs, $config);
$ftp = new Ftp($fs, $config);

$rVer = $ftp->getCurrentVersion();

if(sizeOf($argv) > 1) {
	$sVer = $argv[1];
} else {
	$sVer = $svn->getCurrentVersion();
}



if ($config['debug'])
{
    var_dump($rVer, $sVer, $config);
	exit;
}

if ($sVer != $rVer)
{
    $changes = $svn->checkoutChanges($sVer, $rVer);
    
    echo "\r\n\r\n[ Found " . (count($changes['files'])) . " changes to upload. ]\r\n\r\n";
    
    // Create a .ver file
    $fs->addSvnVersion($sVer);
    
    $changes['files'][] = $config['svn_subfolder'].$config['version_file'];

    $ftp->putChanges($changes);
}
else
{
    echo "\r\n -= Up to date =-";
}

$fs->removeTempFolder();

