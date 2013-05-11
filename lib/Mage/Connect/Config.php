<?php

class Mage_Connect_Config
implements Iterator
{
    protected $_configFile;
    const HEADER = "::ConnectConfig::v::1.0::";
    const DEFAULT_DOWNLOADER_PATH = "downloader";
    const DEFAULT_CACHE_PATH = ".cache";

    protected $properties = array();

    /**
     * @todo
     *
     * @return
     */
    protected function initProperties()
    {
        $this->properties = array (
           'php_ini' => array(
                'type' => 'file',
                'value' => '',
                'prompt' => 'location of php.ini',
                'doc' => "It's a location of PHP.ini to use blah",
                'possible' => '/path/php.ini',
        ),
           'protocol' => array(
                'type' => 'set',
                'value' => 'http',
                'prompt' => 'preffered protocol',
                'doc' => 'preffered protocol',
                'rules' => array('http', 'ftp')
        ),
           'preferred_state' => array(
                'type' => 'set',
                'value' => 'stable',
                'prompt' => 'preferred package state',
                'doc' => 'preferred package state',
                'rules' => array('beta','alpha','stable','devel')
        ),
           'global_dir_mode' => array (
                'type' => 'octal',
                'value' => 0777,
                'prompt' => 'directory creation mode',
                'doc' => 'directory creation mode',
                'possible' => '0777, 0666 etc.',
        ),
           'global_file_mode' => array (
                'type' => 'octal',
                'value' => 0666,
                'prompt' => 'file creation mode',
                'doc' => 'file creation mode',
                'possible' => '0777, 0666 etc.',
        ),
            'downloader_path' => array(
                'type' => 'dir',
                'value' => 'downloader',
                'prompt' => 'relative path, location of magento downloader',
                'doc' => "relative path, location of magento downloader",
                'possible' => 'path',
        ),
            'magento_root' => array(
                'type' => 'dir',
                'value' => '',
                'prompt' => 'location of magento root dir',
                'doc' => "Location of magento",
                'possible' => '/path',
        ),
            'root_channel' => array(
                'type' => 'string',
                'value' => 'core',
                'prompt' => '',
                'doc' => "",
                'possible' => '',
        ),
        
        );

    }
    
    /**
     * @todo
     *
     * @return
     */
    public function getDownloaderPath()
    {
        return $this->magento_root . DIRECTORY_SEPARATOR . $this->downloader_path;
    }
    
    /**
     * @todo
     *
     * @return
     */
    public function getPackagesCacheDir()
    {
        return $this->getDownloaderPath() . DIRECTORY_SEPARATOR . self::DEFAULT_CACHE_PATH;        
    }
    
    /**
     * @todo
     *
     * @param $channel
     * @return
     */
    public function getChannelCacheDir($channel)
    {
        $channel = trim( $channel, "\\/");
        return $this->getPackagesCacheDir(). DIRECTORY_SEPARATOR . $channel; 
    }
    
    /**
     * @todo
     *
     * @param $configFile
     * @return
     */
    public function __construct($configFile = "connect.cfg")
    {
        $this->initProperties();
        $this->_configFile = $configFile;
        $this->load();
    }

    /**
     * @todo
     *
     * @return
     */
    public function getFilename()
    {
        return $this->_configFile;
    }
    
    /**
     * @todo
     *
     * @return
     */
    public function load()
    {
        /**
         * Trick: open in append mode to read,
         * place pointer to begin
         * create if not exists
         */
        $f = fopen($this->_configFile, "a+");
        fseek($f, 0, SEEK_SET);
        $size = filesize($this->_configFile);
        if(!$size) {
            $this->store();
            return;
        }

        $headerLen = strlen(self::HEADER);
        $contents = fread($f, $headerLen);

        if(self::HEADER != $contents) {
            $this->store();
            return;
        }

        $size -= $headerLen;
        $contents = fread($f, $size);

        $data = @unserialize($contents);
        if($data === unserialize(false)) {
            $this->store();
            return;
        }
        foreach($data as $k=>$v) {
            $this->$k = $v;
        }
        fclose($f);
    }

    /**
     * @todo
     *
     * @return
     */
    public function store()
    {
        $data = serialize($this->toArray());
        $f = @fopen($this->_configFile, "w+");
        @fwrite($f, self::HEADER);
        @fwrite($f, $data);
        @fclose($f);
    }

    /**
     * @todo
     *
     * @param $key
     * @param $val
     * @return
     */
    public function validate($key, $val)
    {
        $rules = $this->extractField($key, 'rules');
        if(null === $rules) {
            return true;
        } elseif( is_array($rules) ) {
            return in_array($val, $rules);
        }
        return false;
    }

    /**
     * @todo
     *
     * @param $key
     * @return
     */
    public function possible($key)
    {
        $data = $this->getKey($key);
        if(! $data) {
            return null;
        }
        if('set' == $data['type']) {
            return implode("|", $data['rules']);
        }
        if(!empty($data['possible'])) {
            return $data['possible'];
        }
        return "<".$data['type'].">";
    }

    /**
     * @todo
     *
     * @param $key
     * @return
     */
    public function type($key)
    {
        return $this->extractField($key, 'type');
    }

    /**
     * @todo
     *
     * @param $key
     * @return
     */
    public function doc($key)
    {
        return $this->extractField($key, 'doc');
    }

    /**
     * @todo
     *
     * @param $key
     * @param $field
     * @return
     */
    public function extractField($key, $field)
    {
        if(!isset($this->properties[$key][$field])) {
            return null;
        }
        return $this->properties[$key][$field];
    }

    /**
     * @todo
     *
     * @param $fld
     * @return
     */
    public function hasKey($fld)
    {
        return isset($this->properties[$fld]);
    }

    /**
     * @todo
     *
     * @param $fld
     * @return
     */
    public function getKey($fld)
    {
        if($this->hasKey($fld)) {
            return $this->properties[$fld];
        }
        return null;
    }

    /**
     * @todo
     *
     * @return
     */
    public function rewind()
    {
        reset($this->properties);
    }

    /**
     * @todo
     *
     * @return
     */
    public function valid()
    {
        return current($this->properties) !== false;
    }

    /**
     * @todo
     *
     * @return
     */
    public function key()
    {
        return key($this->properties);
    }

    /**
     * @todo
     *
     * @return
     */
    public function current()
    {
        return current($this->properties);
    }

    /**
     * @todo
     *
     * @return
     */
    public function next()
    {
        next($this->properties);
    }

    /**
     * @todo
     *
     * @param $var
     * @return
     */
    public function __get($var)
    {
        if (isset($this->properties[$var]['value'])) {
            return $this->properties[$var]['value'];
        }
        return null;
    }

    /**
     * @todo
     *
     * @param $var
     * @param $value
     * @return
     */
    public function __set($var, $value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (isset($this->properties[$var])) {
            if ($value === null) {
                $value = '';
            }
            if($this->properties[$var]['value'] !== $value) {
                $this->properties[$var]['value'] = $value;
                $this->store();
            }
        }
    }

    /**
     * @todo
     *
     * @param $withRules
     * @return
     */
    public function toArray($withRules = false)
    {
        $out = array();
        foreach($this as $k=>$v) {
            $out[$k] = $withRules ? $v : $v['value'];
        }
        return $out;
    }
}