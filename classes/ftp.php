<?php

class Ftp
{
    
    /**
     * @var FileSystem
     */
    private $fs;
    
    /**
     * @var array
     */
    private $config;
    
    public function __construct($fs, $config)
    {
        $this->fs = $fs;
        $this->config = $config;
    }
    
    protected function log($msg)
    {
        if ($this->config['verbose'])
        {
            echo "\r\n[FTP] " . $msg . "\r\n";
        }
    }
    
    public function getCurrentVersion()
    {
	    $conn_id = $this->ftpGetConnection();
        
	    $this->log('Connected:' . $conn_id);
	    
	    $this->ftpGoDir($conn_id, $this->config['ftp_root']);
	    
	    $this->log('Went to:' . $this->config['ftp_root']);
        
	    $temp = $this->fs->getTempFolder();
        
	    $this->log('Temp is: '.$temp);
	    
	    $this->log('PWD is: '.ftp_pwd($conn_id));
	    
        $filepath = $this->config['version_file'];
        
	    $this->log('Attempt GET: '.$filepath);
	    

        $success = @ftp_get($conn_id, $temp.$this->config['version_file'], $filepath, FTP_BINARY);
        
        if (!$success)
        {
            $this->log('GET Failed');
            return "-1";
        }
        
        $this->log('GET Success');
        
	    $data = file_get_contents($temp.$this->config['version_file']);
        
        $this->log('FTP Version: ' . $data);
	    
        return $data;
    }
    
	private function getSourceForFile($change) {
		$source = str_replace($this->config['svn_root']. $this->config['svn_subfolder'], "", $change);	
	    $source = $this->fs->getTempFolder() . str_replace('/','\\', $source);
		$source = str_replace('\\','\\\\', $source);	
		return $source;
	}
		
	private function getDestinationForFile($change) {
		return str_replace($this->config['svn_root'] . $this->config['svn_subfolder'], "", $change);	
	}
				
    public function putChanges($changes)
    {
        $conn_id = $this->ftpGetConnection();
        //e cho ftp_pwd($conn_id);exit;
        
        foreach($changes['files'] as $change) {
	        // We want to strip the svn's subfolder from the change.
	        // because that subfolder is exported to the $temp folder.
			$source = $this->getSourceForFile($change);
			//e cho 'source: ' . $source . PHP_EOL;
            // The ftp destination directory.
            $destination = $this->getDestinationForFile($change);
			//e cho 'destination: ' . $destination . PHP_EOL;
			
			if(is_dir($source)) {
				$this->ftpGoDir($conn_id, $destination);
			} else {
				$this->ftpGoDir($conn_id, dirname($destination));
			}
            
			//e cho 'source: ' . $source . PHP_EOL;
            if (is_dir($source)) {
				echo 'created directory ' . $destination . PHP_EOL;
                continue;
            }
 
            
            // upload the file
			$this->log('Source: '.$source);
			$this->log('Destination: '.$source);
			
			echo "Uploading $destination ... ";
            $upload = ftp_put($conn_id, basename($destination), $source, FTP_BINARY); 

            //var_dump($upload, $change, $destination, $source);
            
            // check upload status
            if (!$upload) { 
                echo "\r\nFTP upload has failed! ( " . $upload . " )\r\n";                             
				exit;
            } else {
                //e cho "Uploaded $source to $destination <br />";
                echo "done\r\n";
            }
        }
		
		if($changes['delFiles']) {
			foreach($changes['delFiles'] as $change) {
				$destination = $this->getDestinationForFile($change);
				//e cho '--->DEST DEL: ' . $destination . '<--' . PHP_EOL;
				
				$source = $this->getSourceForFile($change);

				$this->ftpRecursiveDelete($conn_id, $destination);
							
			}
		}
        
        // close the FTP stream 
        ftp_close($conn_id); 
    }
    
    protected function ftpGoDir($conn_id, $dir) {		
        $parts = explode('/', ltrim($dir, '/'));
        
        $current = '/';
        ftp_chdir($conn_id, $current);
        
        foreach($parts as $part) {
            //e cho 'part: ' . $part . PHP_EOL;						
            $current .= $part . '/';
            // Try to navigate
            if (@ftp_chdir($conn_id, $current)) {
                continue;
            }
            
            // Doesn't exist, make it.
			// without this an empty directory \ will be created			
			if($part == "\\") { continue; }
            ftp_mkdir($conn_id, $current);
            ftp_chdir($conn_id, $current);
        }
    }     

	function hasSuffix($filename, $suffix) {
		$extLength = 4;		
		$strpos = strpos($filename, $suffix);
		
		if($strpos == (strlen($filename) - strlen($suffix) - $extLength)) {
			return true;
		}			
		
		return false;
	}
	
	private function ftpRecursiveDelete($conn_id, $directory) {
		//if(ftp_size($conn_id, $directory) == -1) { return; }
		
		# here we attempt to delete the file/directory
		if( ! ( @ftp_rmdir($conn_id, $directory) || @ftp_delete($conn_id, $directory) ) )
		{            
			# if the attempt to delete fails, get the file listing
			$filelist = @ftp_nlist($conn_id, $directory);
			//var_dump($filelist);exit;
			# loop through the file list and recursively delete the FILE in the list
			if($filelist ) {
				foreach($filelist as $file) {    
					
					$parts = explode("/", $file);
					$fileName =  $parts[sizeOf($parts) - 1];
					if($fileName == '.' ||  $fileName == '..') { continue; }
					
					//e cho 'filename: ' . $parts[sizeOf($parts) - 1] . '<--';
					//e cho 'FILE: ' . $file . '<--' . PHP_EOL;
					if($file == ftp_pwd($conn_id)) {
						//e cho 'file is current dir... continue' . PHP_EOL;
						continue;
					}
					if(@ftp_chdir($conn_id, $file)) { 
						// ok it's a dir
						// maybe it's empty?
						//e cho 'trying to remove dir' . PHP_EOL;
						if(@ftp_rmdir($conn_id, $file)) {
						
						} else {					
							//e cho 'no success, recurse...' . PHP_EOL;
							//not empty recurse
							$this->ftpRecursiveDelete($conn_id, $file);	
						}					
						
					} else {
						// must be a file
						//e cho 'must be a file...' . PHP_EOL;
						@ftp_delete($conn_id, $file);
					}
				}
				$this->ftpRecursiveDelete($conn_id, $directory);
			}			
		}
	}
	
    protected function ftpGetConnection()
    {
        $ftp_server = $this->config['server'];
        $ftp_user = $this->config['user'];
        $ftp_pass = $this->config['pass'];
        
        // set up basic connection
        $conn_id = ftp_connect($ftp_server); 
        
        // login with username and password
        $login_result = ftp_login($conn_id, $ftp_user, $ftp_pass); 
        
        // check connection
        if ((!$conn_id) || (!$login_result))
        { 
            echo "FTP connection has failed! <br />";
            echo "Attempted to connect to $ftp_server for user $ftp_user";
            exit; 
        }
        else
        {
            //e cho 'Connected to [[ '.$ftp_server . ' ]] for user [[ '.$ftp_user.' ]]'."\r\n";
        }
        
        // Passive Connection
        ftp_pasv($conn_id, true);
        
        return $conn_id;
    }
}

