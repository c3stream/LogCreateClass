<?php
/**
 * Created by JetBrains PhpStorm.
 * User: PC_103
 * Date: 13/06/04
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace Service;


class LogClass {
    private   $_string;
    private   $_log_file_path;
    private   $_log_file_name;
    private   $_log_name_suffix;
    private   $_file_path;
    private   $_log_enable = true;
    private   $_output_my_class_error = false;
    private   $_my_class_errors = array();
    private   $_my_class_file_log_path;
    private   $_my_class_file_log_name = "myClassInternalErrorLog";
    private   $_log_serial;

    // NEW した時に呼ばれるメソッド
    function __construct($path, $name){

        $this->_log_file_path = $path;
        $this->_log_file_name = $name;
        $this->_log_name_suffix = date('Y-m-d').'.txt';
        $log_serial = substr(md5(uniqid(TRUE, rand())), 0, 10);
        $this->_log_serial = (FALSE === $log_serial)? 'SUBSTR()_FAILED' : $log_serial;

        $internal_encoding =  mb_internal_encoding();
        if($internal_encoding === FALSE){
            throw new \Exception("PHP ERROR! USE FUNCTION : mb_internal_encoding()");
        }
        if($internal_encoding != "UTF-8"){
            throw new \Exception("MUST SET INTERNAL ENCODING UTF-8! NOW SETTING IS : ". $internal_encoding . 'TRY SET mbstring.internal_encoding in php.ini OR mb_internal_encoding("UTF-8")');
        }
        $this->_my_class_file_log_path = dirname(__FILE__)."/LogClassLogs";
        $file_path = $this->_my_class_file_log_path."/".$this->_my_class_file_log_name."_".$this->_log_name_suffix;
        if(!touch($file_path)){
            throw new \Exception("DOSE NOT CREATE FILE! :". $file_path);
        }
        if(!$this->_CheckLogFileHealth($this->_my_class_file_log_path, $this->_my_class_file_log_name)){
            throw new \Exception("SETTING ERROR :". $file_path);
        }
    }

    // 参照が何もなくなった時に自動で呼ばれるメソッド
    function  __destruct(){
        if($this->_output_my_class_error){
            foreach($this->_my_class_errors as $key => $row){
                echo $row;
            }
        }
        $my_class_logs = $this->_my_class_errors;
        unset($this->_my_class_errors);
        foreach($my_class_logs as $key => $row){
            if($this->_CheckLogFileHealth()){
                $this->OutPutLogString($row);
            }
        }
        unset($my_class_logs);
    }

    public function setLogEnable($enable){
        $this->_log_enable = $enable;
    }
    public function setOutputMyClassLogEnable($enable){
        $this->_output_my_class_error = $enable;
    }

    public function OutPutLogString($log_string = ""){
        if ($this->_log_enable === FALSE)
        {
            return false;
        }
        $this->_string = $log_string;

        if(!$this->_CheckLogFileHealth()){
            $this->_my_class_errors[] = "CHECK LOG FILE NG";
            return false;
        } else{
            $this->_FileOpen();
            return true;
        }
    }

    private  function _CheckLogFileHealth($_my_class_file_log_path = "", $_my_class_file_log_name = ""){
        if(!empty($_my_class_file_log_path) && !empty($_my_class_file_log_name)){
            $this->_file_path = $_my_class_file_log_path."/".$_my_class_file_log_name."_".$this->_log_name_suffix;
        } else {
            $this->_file_path =  $this->_log_file_path."/".$this->_log_file_name."_".$this->_log_name_suffix;
        }
        try{
            if(!file_exists($this->_file_path)){
                $this->_my_class_errors[] = "NO FILE EXISTS!SHOULD LOOK FILE PATH : ". $this->_file_path;
                return false;
            } else {
                clearstatcache(TRUE, $this->_file_path);
            }
        } catch (\Exception $e){
            $this->_my_class_errors[] = $e->getMessage();
            return false;
        }

        try {
            if(!is_writable($this->_file_path)){
                $this->_my_class_errors[] = "NO FILE WRITABLE!SHOULD LOOK FILE MOD : ". $this->_file_path;
                return false;
            } else {
                clearstatcache(TRUE, $this->_file_path);
            }
        } catch (\Exception $e){
            $this->_my_class_errors[] = $e->getMessage();
            return false;
        }

        return true;
    }

    private function _FileOpen(){
        $fp = null;
        try{
            $fp = fopen($this->_file_path, 'a');
        } catch(\Exception $e){
            $this->_my_class_errors[] =  $e->getMessage();
            return false;
        }
        $this->_FileLock($fp);
        return true;
    }

    private function _FileLock($fp){
        try{
            if(!flock($fp, LOCK_EX)){
                $this->_my_class_errors[] = "FILE LOCK FAILED";
                return false;
            }
        } catch(\Exception $e){
            $this->_my_class_errors[] =  $e->getMessage();
            return false;
        }
        $this->_FileWrite($fp);
        return true;
    }

    private function _FileWrite($fp){
        $log_string = sprintf("[%s - %s] %s"."\n", date('Y/m/d H:i:s'), $this->_log_serial, $this->_string);
        try{
            if(!fwrite($fp, $log_string)){
                $this->_my_class_errors[] = "FILE WRITE FAILED";
                return false;
        }
        } catch(\Exception $e){
            $this->_my_class_errors[] = $e->getMessage();
            return false;
        }
        $this->_FileUnLock($fp);
        return true;
    }
    private function _FileUnLock($fp){
        try{
            if(!flock($fp, LOCK_UN)){
                $this->_my_class_errors[] = "FILE UNLOCK FAILED";
                return false;
            }
        } catch(\Exception $e){
            $this->_my_class_errors[] = $e->getMessage();
            return false;
        }
        $this->_FileClose($fp);
        return true;
    }

    private function _FileClose($fp){
        try{
            if(!fclose($fp)){
                $this->_my_class_errors[] = "FILE CLOSE FAILED";
                return false;
            }
        } catch(\Exception $e){
            $this->_my_class_errors[] = $e->getMessage();
            return false;
        }
        return true;
    }

}