<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message\Stream;

use Exception;
use Psr\Http\Message\StreamInterface;
use function fclose;
use function feof;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function fwrite;
use function rewind;
use function stream_get_contents;
use function stream_get_meta_data;

class StringStream implements StreamInterface
{
    /**
     * @var false|resource
     */
    protected $stream;

    public function __construct(string $string)
    {
        $this->stream = fopen('php://memory', 'rw+');
        $this->write($string);
        $this->rewind();
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            $this->close();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return stream_get_contents($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        fclose($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $content = $this->getContents();
        $this->close();
        $this->stream = null;
        return $content;
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function getSize()
    {
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            return $stats['size'];
        }
        throw new Exception('Cannot stat stream size.');
    }

    /**
     * @return false|int
     */
    public function tell()
    {
        return ftell($this->stream);
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * @return bool|void
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    /**
     * @param int $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->stream, $offset, $whence);
    }

    public function rewind()
    {
        rewind($this->stream);
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * @param string $string
     *
     * @return false|int
     */
    public function write($string)
    {
        return (int) fwrite($this->stream, $string);
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * @param int $length
     *
     * @return false|string
     */
    public function read($length)
    {
        return fread($this->stream, $length);
    }

    /**
     * @return false|string
     */
    public function getContents()
    {
        return $this->__toString();
    }

    /**
     * @param null $key
     *
     * @return null|array|mixed|void
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        return $key ? $meta[$key] ?? null : $meta;
    }
}
