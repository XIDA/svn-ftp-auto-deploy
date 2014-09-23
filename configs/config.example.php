<?php
	return array(
		'name'			 => 'example name here',
		'verbose'		 => false,					// Logging
		'debug'			 => false,					// Mode
		'version_file'	 => 'deploy.ver',
		'svn' => array(							// SVN
			'root'		 => '',
			'subfolder'	 => '',
			'ignore'	 => array(
				'nbproject/'
			),
			'username'	 => '',
			'password'	 => '',
		),
		'ftp' => array(								// FTP

			'root' => '',
			// FTP Auth
			'server'	 => '',
			'username'	 => '',
			'password'	 => '',
		)
	);
?>