<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/5
 * Time: 21:11
 */
namespace System\Core\Storage;

abstract class StorageDriver {

    abstract public function read($filepath,$file_encoding='UTF-8',$output_encode='UTF-8');

    abstract public function write($filepath,$content,$write_encode='UTF-8');

    abstract public function append($filepath,$content,$write_encode='UTF-8');

    abstract public function has($filepath);

    abstract public function touch($filename, $time = null, $atime = null);

    abstract public function unlink($filepath);

    abstract public function info($filepath,$type=null);

    abstract public function readFolder($path,$clear=true);

    abstract public function removeFolder($dirpath,$recursion=false);

    abstract public function makeFolder($dirpath,$auth = 0755);

}