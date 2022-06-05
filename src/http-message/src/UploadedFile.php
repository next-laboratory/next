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

namespace Max\HttpMessage;

use Exception;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use SplFileInfo;

class UploadedFile implements UploadedFileInterface
{
    protected const ERROR_MESSAGES = [
        UPLOAD_ERR_OK         => 'OK.',
        UPLOAD_ERR_INI_SIZE   => '上传的文件超过了 php.ini 中 upload_max_filesize选项限制的值',
        UPLOAD_ERR_FORM_SIZE  => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
        UPLOAD_ERR_PARTIAL    => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE    => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
    ];

    /**
     * @param StreamInterface|null $stream
     * @param int                  $size
     * @param string               $clientFilename
     * @param string               $clientMediaType
     * @param int                  $error
     */
    public function __construct(
        protected ?StreamInterface $stream = null,
        protected int              $size = 0,
        protected string           $clientFilename = '',
        protected string           $clientMediaType = '',
        protected int              $error = \UPLOAD_ERR_OK,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param $targetPath
     *
     * @return SplFileInfo
     * @throws Exception
     */
    public function moveTo($targetPath)
    {
        if (($code = $this->getError()) > 0) {
            throw new RuntimeException(static::ERROR_MESSAGES[$code], $code);
        }
        $path = pathinfo($targetPath, PATHINFO_DIRNAME);
        !is_dir($path) && mkdir($path, 0755, true);
        if (move_uploaded_file($this->stream->getMetadata('uri'), $targetPath)) {
            return new SplFileInfo($targetPath);
        }
        throw new RuntimeException('文件上传失败，请检查目录权限！');
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}
