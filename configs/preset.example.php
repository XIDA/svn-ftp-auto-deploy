<?php
	return array(
		'name'			 => 'framework',
		'verbose'		 => false,					// Logging
		'debug'			 => false,					// Mode
		'version_file'	 => 'deploy.ver',
		'svn' => array(							// SVN
			'root'		 => 'https://url.to/framework/',
			'subfolder'	 => 'trunk/',
			'ignore'	 => array(
				'nbproject/'
			),
			'username'	 => '',
			'password'	 => '',
		),
	);
?>
