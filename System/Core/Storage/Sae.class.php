<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/6
 * Time: 11:21
 */
namespace System\Core\Storage;

class Sae extends  StorageDriver {

    public function __construct(){

    }

    public function read($filepath, $file_encoding = 'UTF-8', $output_encode = 'UTF-8')
    {
        // TODO: Implement read() method.
    }

    public function write($filepath, $content, $write_encode = 'UTF-8')
    {
        // TODO: Implement write() method.
    }

    public function append($filepath, $content, $write_encode = 'UTF-8')
    {
        // TODO: Implement append() method.
    }

    public function has($filepath)
    {
        // TODO: Implement has() method.
    }

    public function touch($filename, $time = null, $atime = null)
    {
        // TODO: Implement touch() method.
    }

    public function unlink($filepath)
    {
        // TODO: Implement unlink() method.
    }

    public function info($filepath, $type = null)
    {
        // TODO: Implement info() method.
    }

    public function readFolder($path, $clear = true)
    {
        // TODO: Implement readFolder() method.
    }

    public function removeFolder($dirpath, $recursion = false)
    {
        // TODO: Implement removeFolder() method.
    }

    public function makeFolder($dirpath, $auth = 0755)
    {
        // TODO: Implement makeFolder() method.
    }

}