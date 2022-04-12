<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Http\Message;

use Exception;
use Max\Http\Message\Stream\FileStream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use SplFileInfo;

class UploadedFile implements UploadedFileInterface
{
    /**
     * 错误提示
     */
    protected const ERROR = [
        'UPLOAD_ERR_OK',         //表示没有错误发生，文件上传成功。
        'UPLOAD_ERR_INI_SIZE',   //上传的文件超过了 php.ini 中 upload_max_filesize选项限制的值。
        'UPLOAD_ERR_FORM_SIZE',  //上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
        'UPLOAD_ERR_PARTIAL',    //文件只有部分被上传。
        'UPLOAD_ERR_NO_FILE',    //没有文件被上传。
        'UPLOAD_ERR_NO_TMP_DIR', //找不到临时文件夹。
        'UPLOAD_ERR_CANT_WRITE', //文件写入失败。
    ];

    /**
     * @var string|mixed
     */
    protected string $name = '';

    /**
     * @var string|mixed
     */
    protected string $type = '';

    /**
     * @var string|mixed
     */
    protected string $tmpName = '';

    /**
     * @var int|mixed
     */
    protected int $error = 0;

    /**
     * @var int|mixed
     */
    protected int $size = 0;

    /**
     * @param array $file
     */
    public function __construct(array $file)
    {
        $this->name    = $file['name'];
        $this->tmpName = $file['tmp_name'];
        $this->type    = $file['type'];
        $this->error   = $file['error'];
        $this->size    = $file['size'];
    }

    /**
     * @return StreamInterface
     * @throws Exception
     */
    public function getStream()
    {
        return new FileStream($this->tmpName);
    }

    /**
     * @param $targetPath
     *
     * @return SplFileInfo
     * @throws Exception
     */
    public function moveTo($targetPath)
    {
        if (0 !== $this->getError()) {
            throw new Exception(static::ERROR[$this->getError()], $this->getError());
        }
        $path = pathinfo($targetPath, PATHINFO_DIRNAME);
        !is_dir($path) && mkdir($path, 0755, true);
        if (move_uploaded_file($this->tmpName, $targetPath)) {
            return new SplFileInfo($targetPath);
        }
        throw new Exception('文件上传失败，请检查目录权限！');
    }

    /**
     * @return int|mixed|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int|mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed|string|null
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * @return mixed|string|null
     */
    public function getClientMediaType()
    {
        return $this->type;
    }
}
