<?php

class Upload
{
    private string $path;
    private bool $randName;
    private int $max_size;
    private array $allowType;

    private string $tmp_name;
    private string $originName;
    private int $size;
    private int $errorCode;
    private string $errorMsg;
    private string $newName;
    private string $type;

    //设置成员属性的方法,可以连贯操作
    public function __construct($path = "./file", $randName = true, $max_size = 100000, $allowType = ["png", "gif", "jpg"])
    {
        $this->path = $path;
        $this->randName = $randName;
        $this->max_size = $max_size;
        $this->allowType = $allowType;
    }

    function set($var, $val)
    {
        $this->$var = $val;
        return $this;
    }

    public function uploadFile($field)
    {
        if (!$this->checkPath()) {
            return FALSE;
        }
        $name = $_FILES[$field]["name"];
        $tmp_name = $_FILES[$field]["tmp_name"];
        $size = $_FILES[$field]["size"];
        $error = $_FILES[$field]["error"];

        if (is_array($name)) {
            $arr = null;
            for ($i = 0; $i < count($name); $i++) {
                if ($name[$i] == '')
                    continue;
                if ($this->setFile($name[$i], $tmp_name[$i], $size[$i], $error[$i])) {
                    if ($this->checkSize() && $this->checkType()) {
                        if ($this->moveFile()) {
                            $arr[] = $this->path . "/" . $this->newName;
                            continue;
                        } else return FALSE;
                    } else return FALSE;
                } else return FALSE;
            }
            return json_encode($arr);
        } else {
            if ($this->setFile($name, $tmp_name, $size, $error)) {
                if ($this->checkSize() && $this->checkType()) {
                    if ($this->moveFile()) {
                        return json_encode($this->path . "/" . $this->newName);
                    } else return FALSE;
                } else return FALSE;
            } else return FALSE;
        }
    }

    private function setFile($name, $tmp_name, $size, $error)
    {
        $this->errorCode = $error;
        if ($error)
            return FALSE;
        $this->originName = $name;
        $this->tmp_name = $tmp_name;
        $this->size = $size;
        return true;
    }

    private function checkSize()
    {
        if ($this->size > $this->max_size)
            return FALSE;
        return TRUE;
    }

    private function checkPath()
    {
        if (empty($this->path)) {
            //$this->path = "./files";
            return FALSE;
        } elseif (!file_exists($this->path) || !is_writable($this->path)) {
            if (!mkdir($this->path, 0777, true))
                return FALSE;
        }
        return TRUE;
    }

    //随机命名
    private function reName()
    {
        if ($this->randName) {
            $this->newName = date("his") . rand(0, 1000) . "." . $this->type;
            return TRUE;
        } else {
            $this->newName = $this->originName;
            return FALSE;
        }
    }

    //获取文件格式
    private function checkType()
    {
        $name = explode(".", $this->originName);
        $type = array_pop($name);
        if (!in_array($type, $this->allowType))
            return FALSE;
        else $this->type = $type;
        return TRUE;
    }

    private function getError()
    {
        switch ($this->errorCode) {
            case 4:
                $err = "没有文件被上传！";
                break;
            case 3:
                $err = "文件被部分上传！";
                break;
            case 2:
                $err = "文件大小超过HTLML限制的MAX_FILE_SIZE！";
                break;
            case 1:
                $err = "文件大小超过PHP限制的upload_max_filesize！";
                break;
            case -1:
                $err = "文件格式不支持上传！";
                break;
            case -2:
                $err = "文件大小超过限制！";
                break;
            default :
                $err = "未知错误！";
                break;
        }
    }

    private function moveFile()
    {
        $this->reName();
        if ($this->errorCode) {
            return FALSE;
        }
        if (move_uploaded_file($this->tmp_name, $this->path . "/" . $this->newName)) {
            return TRUE;
        }
        return FALSE;
    }
}
