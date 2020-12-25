<?php

namespace App\Index\Controller;

use Yao\Db;
use Yao\Facade\File;
use \Yao\Facade\Request;

class Index
{
    public function index()
    {
        if (Request::isMethod('get')) {
            $file = Db::name('files')->field(['file', 'filename', 'md5'])->select()->toArray();
            return view('index@index', ['file' => $file]);
        }
        try {
            $res = \Yao\Facade\File::data(Request::file('file'))
                ->validate(['extExcept' => ['php', 'sql', 'sh', 'html'], 'size' => 1024 * 1024 * 10])
                ->move('upload/');
            Db::name('files')->insert(['file' => $res['path'], 'filename' => $res['filename'], 'create_time' => date('Y-m-d h-i-s'), 'md5' => md5_file($res['path'])]);
            $message = '上传成功！';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return "<script>alert('$message');window.location.href='/'</script>";
    }

    public function todo()
    {
        return view('index@todo');
    }

    public function download(\Yao\Http\Request $request)
    {
        $file = Db::name('files')->where(['md5' => $request->get('hash')])->find();
        if (!empty($file)) {
            return File::download($file['filename'], $file['file']);
        }
    }
}
