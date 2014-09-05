<?php

class Svn
{
    
    /**
     * @var FileSystem
     */
    private $fs;
    
    /**
     * @var array
     */
    private $config;
    
    public function __construct($fs, $config) {
        $this->fs = $fs;
        $this->config = $config;
    }
    
    public function checkoutChanges($targetRev, $rVer) {
        $changes = $this->getRecentChanges($targetRev, $rVer);
		//e cho '...' . PHP_EOL;
        //v ar_dump($changes);		
		
		echo 'exporting files from svn' . PHP_EOL;
		
        foreach($changes['files'] as $f) {
			$cItem = array();
            $path = $this->config['svn_root'].$f;
			
			$file = str_replace($this->config['svn_root'] . $this->config['svn_subfolder'], "", $f);
			echo 'exporting ' . $file . PHP_EOL;
			
            //$file = substr($f, strlen($this->config['svn_subfolder']) - 1);
			//e cho "file: " . $file . PHP_EOL;
            $target = $this->fs->getTempFolder() . str_replace('/','\\', $file);
			//e cho 'target: ' . $target . '<--' . PHP_EOL; 
			
            //var_dump($target, $file, $f, $path); exit;
            
            // Ensure Directory Exists
            $this->fs->ensureFolderExists($target);
            
            $cmd = 'svn export --force ' . $f . '@' .  $targetRev . ' ' . $target;
			//e cho 'cmd: ' . $cmd . '<--' . PHP_EOL;
			
            exec($cmd);
        }
        
        $svn_ver = $this->getSvnVersion();
        $this->fs->addSvnVersion($svn_ver);
        
        return $changes;
    }
    
    protected function getRecentChanges($sVer, $rVer) {
        $raw_log = $this->getChangeLog($sVer, $rVer);
        
        $changes = $this->getChangeArr($raw_log);
        
        return $changes;
    }
    
    public function getCurrentVersion() {
        return $this->getSvnVersion();
    }
    
    protected function getSvnVersion() {	
        $cmd = 'svn info '.$this->config['svn_root'];
        $x = exec($cmd, $result);
        
        $str = implode(' ', $result);
        
        //var_dump($result,$cmd, $x, $str);
        preg_match('/Revision: ([0-9]+)/', $str, $matches);
        
        return $matches[1];
    }
    
    protected function getChangeLog($targetVer, $ftpVersion) {        
        // We want the subfolder here because we only need to export
        // the files that should be uploaded.
        $repo = $this->config['svn_root'].$this->config['svn_subfolder'];
        
		//check if the file is there at all
        $cmd = 'svn log -r 1:HEAD --limit 1 ' . $repo;
		
		$exec = exec($cmd, $out);
		
		// this happens if your desired path isn't in the svn anymore
		// for example if the svn subdir was deleted
		if(sizeOf($out) == 0) {
			echo 'error: there is a problem with the path you a trying to check out' . PHP_EOL;
			echo 'use the following command to find out what\'s wrong' . PHP_EOL;
			echo $cmd . PHP_EOL;			
			exit;
		}

		// we have to check if the svn path is available at the desired revision
		// we use svn log for that and the output looks like this
		// r41345 | bk | 2014-09-04 14:57:16 +0200 (Do, 04 Sep 2014) | 1 line\n\nmega changes\n
		// so after the r we will find the revision number, let's use regex
		$re = "/r([0-9]*) \\|/";     
		preg_match($re, $out[1], $matches);		
		// matches[1] contains the revision number
		$firstRevisionOfSVNPath = $matches[1];

		if($firstRevisionOfSVNPath > $targetVer) {
			echo 'error: you are trying to update to revision ' . $targetVer . ' but the first revision of your svn path is ' . $firstRevisionOfSVNPath . PHP_EOL;
			echo 'use the following command to find out what\'s wrong' . PHP_EOL;
			echo $cmd . PHP_EOL;			
			exit;		
		}
		
		$cmd = 'svn diff ' . $repo . ' --summarize -r '.$ftpVersion.':' . $targetVer;
        //e cho $cmd . PHP_EOL;		
        
        $out = null;
        $return = null;
        $exec = exec($cmd, $out);
        
        return $out;
    }
    
    protected function getChangeArr($lines) {
		$delFiles = array();
		$files = array();
		
		$repo = $this->config['svn_root'].$this->config['svn_subfolder'];
		$repo = rtrim($repo,"/");
		
        $totLines = count($lines);
        for($i=0;$i<$totLines;$i++) {
            //e cho "\nInside FOR i = $i";
            $curLine = $lines[$i];
            //remove \r and \n
            $curLine = str_replace("\r", "", $curLine);
            $curLine = str_replace("\n", "", $curLine);
            
			$parts = explode(" ", $curLine);
			$sts = $parts[0];
			$file = $parts[7];
			$file = rtrim($file,"/");
			
			//if the file url is the same as the repo url, we remove it
			if($file == $repo) {
				continue;
			}
			
			if($sts == 'D') {
				$delFiles[] = $file;
			} else {
				$files[] = $file;
			}				
        }
        //e cho "\r\n".'Completed SVN Parsing'."\r\n";

		$returnArray = array();
		$returnArray['files'] = array_unique($files);		
		$returnArray['delFiles'] = array_unique($delFiles);		

        return $returnArray;
    }
    
}

