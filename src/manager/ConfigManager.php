<?php
namespace App\Manager;

class ConfigManager implements \ArrayAccess
{

    private $path;
    private $config;
    private static $instance;

    public function __construct()
    {
        $this->path = __DIR__. '/../../config/';
    }

    public static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }


    public function offsetGet($offset)
    {
        if(empty($this->config))
        {
            $this->config[$offset] = require $this->path.$offset.'.php';
        }
        return $this->config[$offset];
    }


    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }


    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    final private function __clone()
    {
        // TODO: Implement __clone() method.
    }
}