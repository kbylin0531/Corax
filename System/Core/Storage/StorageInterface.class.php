<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/5
 * Time: 21:11
 */
namespace System\Core\Storage;

interface StorageInterface {

    public function read($filepath,$file_encoding='UTF-8',$output_encode='UTF-8');

    public function write($filepath,$content,$write_encode='UTF-8');

    public function append($filepath,$content,$write_encode='UTF-8');

    public function has($filepath);

    public function touch($filename, $time = null, $atime = null);

    public function unlink($filepath);

    public function info($filepath,$type=null);

    public function readFolder($path,$clear=true);

    public function removeFolder($dirpath,$recursion=false);

    public function makeFolder($dirpath,$auth = 0755);

}