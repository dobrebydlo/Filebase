<?php  namespace Filebase;


class Backup
{

    /**
    * $backupLocation
    *
    * Current backup location..
    * $backupLocation
    */
    protected $backupLocation;


    //--------------------------------------------------------------------


    /**
    * $config
    *
    * Stores all the configuration object settings
    * \Filebase\Config
    */
    protected $config;


    //--------------------------------------------------------------------


    /**
    * __construct
    *
    */
    public function __construct($backupLocation = '', Config $config)
    {
        $this->backupLocation = $backupLocation;
        $this->config = $config;

        // Check directory and create it if it doesn't exist
        if (!is_dir($this->backupLocation))
        {
            if (!@mkdir($this->backupLocation, 0777, true))
            {
                throw new \Exception(sprintf('`%s` doesn\'t exist and can\'t be created.', $this->backupLocation));
            }
        }
        else if (!is_writable($this->backupLocation))
        {
            throw new \Exception(sprintf('`%s` is not writable.', $this->backupLocation));
        }
    }


    //--------------------------------------------------------------------


    /**
    * save()
    *
    */
    public function save()
    {
        $backupFile = $this->backupLocation.'/'.time().'.zip';

        if ($results = $this->zip($this->config->dir, $backupFile))
        {
            return $backupFile;
        }

        throw new \Exception('Error backing up database.');
    }


    //--------------------------------------------------------------------


    /**
    * find()
    *
    * Returns an array of all the backups currently available
    *
    */
    public function find()
    {
        $backups = [];
        $files = glob(realpath($this->backupLocation)."/*.zip");
        foreach($files as $file)
        {
            $basename = str_replace('.zip','',basename($file));
            $backups[$basename] = $file;
        }

        return $backups;
    }


    //--------------------------------------------------------------------


    /**
    * clean()
    *
    * Clears and deletes all backups (zip files only)
    *
    */
    public function clean()
    {
        return array_map('unlink', glob(realpath($this->backupLocation)."/*.zip"));
    }


    //--------------------------------------------------------------------


    /**
    * zip()
    *
    * Prevents the zip from zipping up the storage diretories
    *
    */
    protected function zip($source = '', $target = '')
    {
        if (extension_loaded('zip'))
        {
           if (file_exists($source))
           {
               $zip = new \ZipArchive();
               if ($zip->open($target, \ZIPARCHIVE::CREATE))
               {
                   $source = realpath($source);
                   if (is_dir($source))
                   {
                       $iterator = new \RecursiveDirectoryIterator($source);
                       $iterator->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);
                       $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
                       foreach ($files as $file)
                       {
                           $file = realpath($file);

                           if (preg_match('|'.realpath($this->backupLocation).'|',$file))
                           {
                               continue;
                           }

                           if (is_dir($file))
                           {
                               $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                           }
                           else if (is_file($file))
                           {
                               $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                           }
                       }

                   }
                   else if (is_file($source))
                   {
                       $zip->addFromString(basename($source), file_get_contents($source));
                   }
               }

               return $zip->close();
           }
       }

       return false;
    }



}