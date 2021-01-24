<?php
namespace Util\Isdoc;

// inspiration:
// create ZIP archive from strings
// https://www.php.net/manual/en/ziparchive.addfromstring.php

// create ZIP file in temp dir
// https://bnks.xyz/avoiding-permissions-problems-creating-zip-files-php/

class IsdocZipArchive
{
    public $name;
    public $zip;
    public $tmpName;
    public $resource;
    
    public function __construct($name)
    {
        chdir( sys_get_temp_dir() ); // Zip always get's created in current working dir so move to tmp.
        
        $this->name = $name;
        $this->zip = new \ZipArchive;
        $this->tmpName = uniqid(); // Generate a temp UID for the file on disk.
        $this->resource = $this->zip->open($this->tmpName, \ZipArchive::CREATE);
    }
    
    public function addFileString($file_name, $file_content, $directory = false)
    {
        if ($this->resource === TRUE) {
            $path = null;
            if (!empty($directory)) {
                $path = $directory.'/';
            }
            $this->zip->addFromString($path.$file_name, $file_content);
        } else {
            // echo 'failed';
        }
    }
    
    public function output() {
        $this->zip->close();
        
        // now download ZIP
        // https://stackoverflow.com/questions/12225964/create-a-zip-file-and-download-it
        header("Content-type: application/zip"); 
        header("Content-Disposition: attachment; filename=$this->name");
        header("Content-length: " . filesize($this->tmpName));
        header("Pragma: no-cache"); 
        header("Expires: 0"); 
        readfile("$this->tmpName");
        unlink( $this->tmpName );
        exit;
    }
}
