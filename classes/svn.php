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
    
    public function __construct($fs, $config)
    {
        $this->fs = $fs;
        $this->config = $config;
    }
    
    public function checkoutChanges($targetRev, $rVer)
    {
        $changes = $this->getRecentChanges($targetRev, $rVer);
		//e cho '...' . PHP_EOL;
        //v ar_dump($changes);
		
        foreach($changes['files'] as $f)
        {
			$cItem = array();
            $path = $this->config['svn_root'].$f;
			
			$file = str_replace($this->config['svn_root'], "", $f);
            //$file = substr($f, strlen($this->config['svn_subfolder']) - 1);
			//e cho "file: " . $file . PHP_EOL;
            $target = $this->fs->getTempFolder() . str_replace('/','\\', $file);
			//e cho 'target: ' . $target . '<--' . PHP_EOL; 
			
            //var_dump($target, $file, $f, $path); exit;
            
            // Ensure Directory Exists
            $this->fs->ensureFolderExists($target);
            
            $cmd = 'svn export ' . $f . '@' .  $targetRev . ' ' . $target;
			//e cho 'cmd: ' . $cmd . '<--' . PHP_EOL;
			
            exec($cmd);
        }
        
        $svn_ver = $this->getSvnVersion();
        $this->fs->addSvnVersion($svn_ver);
        
        return $changes;
    }
    
    protected function getRecentChanges($sVer, $rVer)
    {
        $raw_log = $this->getChangeLog($sVer, $rVer);
        
        $changes = $this->getChangeArr($raw_log);
        
        return $changes;
    }
    
    public function getCurrentVersion()
    {
        return $this->getSvnVersion();
    }
    
    protected function getSvnVersion()
    {
        $cmd = 'svn info '.$this->config['svn_root'];
        $x = exec($cmd, $result);
        
        $str = implode(' ', $result);
        
        //var_dump($result,$cmd, $x, $str);
        preg_match('/Revision: ([0-9]+)/', $str, $matches);
        
        return $matches[1];
    }
    
    protected function getChangeLog($targetVer, $ftpVersion)
    {
        //$remote_ver = $this->getRemoteVersion();
        
        // We want the subfolder here because we only need to export
        // the files that should be uploaded.
        $repo = $this->config['svn_root'].$this->config['svn_subfolder'];
        
        //$cmd = 'svn log ' . $repo . ' -v -r'.$ftpVersion.':' . $targetVer;
		$cmd = 'svn diff ' . $repo . ' --summarize -r'.$ftpVersion.':' . $targetVer;
        //e cho $cmd . "\r\n";
        
        $out = null;
        $return = null;
        $exec = exec($cmd, $out);
        
        return $out;
    }
    
    protected function getChangeArr($lines) {
	
        $totLines = count($lines);
        for($i=0;$i<$totLines;$i++)
        {
            //e cho "\nInside FOR i = $i";
            $curLine = $lines[$i];
            //remove \r and \n
            $curLine = str_replace("\r", "", $curLine);
            $curLine = str_replace("\n", "", $curLine);
            
			$parts = explode(" ", $curLine);
			$sts = $parts[0];
			$file = $parts[7];
			if($sts == 'D')
			{
				$delFiles[] = $file;
			}
			else
			{
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

