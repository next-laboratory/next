<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

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
        $this->stream = fopen('php://temp', 'rw+');
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
     */
    public function getSize()
    {
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            return $stats['size'];
        }
        throw new RuntimeException('Cannot stat stream size.');
    }

    /**
     * {@inheritDoc}
     */
    public function tell(): int
    {
        return ftell($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function eof(): bool
    {
        return feof($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable(): bool
    {
        return (bool) $this->getMetadata('seekable');
    }

    /**
     * {@inheritDoc}
     *
     * @param int $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->stream, $offset, $whence);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        rewind($this->stream);
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function write($string): int
    {
        return (int) fwrite($this->stream, $string);
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($length): string
    {
        if ($content = fread($this->stream, $length)) {
            return $content;
        }
        throw new RuntimeException('Cannot read from stream');
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(): string
    {
        return $this->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        return $key ? $meta[$key] ?? null : $meta;
    }
}
