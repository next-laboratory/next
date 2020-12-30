<?php

namespace App\Index\Controller;

use Yao\{
    Db
};
use Yao\Facade\{
    File,
    Request,
    Route
};
use Yao\Route\Alias;

class Index
{
    public function test(\Yao\Http\Request $request)
    {
        dump(url('index@name'));
        // dump((new Alias())->get());
    }

    public function index()
    {
        $file = Db::name('files')->field(['file', 'filename', 'md5'])->order(['id' => 'desc'])->select()->toArray();
        foreach ($file as $k => $v) {
            $file[$k]['size'] = format_size(filesize($file[$k]['file']));
            unset($file[$k]['file']);
        }
        header('Access-Control-Allow-Origin:*');
        return $file;
    }

    public function upload()
    {
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:*');
        header('Access-Control-Allow-Headers:content-type');
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
