<?php

namespace App\Index\Controller;

use \Yao\Facade\Request;

class Index
{
    public function index()
    {
        if (Request::isMethod('get')) {
            $file = glob('./upload/*');
            return view('index@index', ['file' => $file]);
        }
        try {
            $file = Request::file('file');
            $file = \Yao\Facade\File::data($file)->validate(['ext' => ['rar', 'zip'], 'extExcept' => ['php', 'sql', 'sh', 'html'], 'size' => 1024 * 1024 * 10])->move('upload/');
            $message = '上传成功！';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        echo "<script>alert('$message');window.location.href='/'</script>";
    }
}
