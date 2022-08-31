<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use SplFileInfo;

class UploadedFile implements UploadedFileInterface
{
    protected const ERROR_MESSAGES = [
        UPLOAD_ERR_OK         => 'File uploaded successfully.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeded the limit for upload_max_filesize in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The size of the uploaded file exceeds the value specified by the MAX_FILE_SIZE option in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'Only part of the file was uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No files were uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Unable to find temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'File write failed.',
    ];

    /**
     * @param null|StreamInterface $stream          文件流
     * @param int                  $size            文件大小
     * @param string               $clientFilename  客户端文件名
     * @param string               $clientMediaType 客户端媒体类型
     * @param int                  $error           错误码
     */
    public function __construct(
        protected ?StreamInterface $stream = null,
        protected int $size = 0,
        protected string $clientFilename = '',
        protected string $clientMediaType = '',
        protected int $error = \UPLOAD_ERR_OK,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getStream(): ?StreamInterface
    {
        return $this->stream;
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo($targetPath): SplFileInfo
    {
        if (($code = $this->getError()) > 0) {
            throw new RuntimeException(static::ERROR_MESSAGES[$code], $code);
        }
        $path = pathinfo($targetPath, PATHINFO_DIRNAME);
        ! is_dir($path) && mkdir($path, 0755, true);
        if (move_uploaded_file($this->stream->getMetadata('uri'), $targetPath)) {
            return new SplFileInfo($targetPath);
        }
        throw new RuntimeException('Failed to upload file. Check directory permission.');
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
