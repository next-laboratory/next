<?php
declare(strict_types=1);

namespace Max\HttpMessage\Stream;

use Exception;
use Psr\HttpMessage\StreamInterface;
use function fclose;
use function feof;
use function fopen;
use function fread;
use function fstat;
use function ftell;
use function stream_get_contents;
use function stream_get_meta_data;

class FileStream implements StreamInterface
{
    protected const READ_WRITE_HASH = [
        'read'  => [
            'r'   => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb'  => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w'   => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+'  => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    /**
     * @var false|resource
     */
    protected $stream;

    /**
     * FileStream constructor.
     *
     * @param string $path
     *
     * @throws Exception
     */
    public function __construct(string $path)
    {
        $this->stream = fopen($path, 'rw+');
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return stream_get_contents($this->stream);
    }

    /**
     *
     */
    public function close()
    {
        fclose($this->stream);
    }

    /**
     *
     */
    public function detach()
    {
        // TODO: Implement detach() method.
    }

    /**
     * @return int|null
     * @throws Exception
     */
    public function getSize()
    {
        $stats = fstat($this->stream);

        return $stats['size'] ?? throw new Exception('Cannot stat stream size.');
    }

    /**
     *
     */
    public function tell()
    {
        ftell($this->stream);
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * @return array|mixed|null
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    /**
     * @param     $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->stream, $offset, $whence);
    }

    /**
     *
     */
    public function rewind()
    {
        rewind($this->stream);
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return static::READ_WRITE_HASH['write'][$this->getMetadata('mode')];
    }

    /**
     * @param $string
     */
    public function write($string)
    {
        fwrite($this->stream, $string);
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return static::READ_WRITE_HASH['read'][$this->getMetadata('mode')];
    }

    /**
     * @param $length
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
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        return $key ? $meta[$key] ?? null : $meta;
    }
}
