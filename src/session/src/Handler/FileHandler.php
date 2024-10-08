<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Session\Handler;

class FileHandler implements \SessionHandlerInterface
{
    public function __construct(
        protected int $gcDivisor = 100,
        protected int $gcProbability = 1,
        protected int $gcMaxLifetime = 1440,
        protected string $path = '',
    ) {
        if (! $this->path) {
            $this->path = sys_get_temp_dir();
        }
        ! \is_dir($this->path) && \mkdir($this->path, 0755, true);
    }

    /**
     * @param int $maxLifeTime
     */
    #[\ReturnTypeWillChange]
    public function gc($maxLifeTime): false|int
    {
        try {
            $number = 0;
            $now    = \time();
            $files  = $this->findFiles($this->path, function (\SplFileInfo $item) use ($maxLifeTime, $now) {
                return $now - $maxLifeTime > $item->getMTime();
            });

            foreach ($files as $file) {
                $this->unlink($file->getPathname());
                ++$number;
            }
            return $number;
        } catch (\Throwable) {
            return false;
        }
    }

    public function delete(string $id): bool
    {
        try {
            return $this->unlink($this->getSessionFile($id));
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @param string $id
     */
    #[\ReturnTypeWillChange]
    public function read($id): false|string
    {
        $sessionFile = $this->getSessionFile($id);
        if (\file_exists($sessionFile)) {
            return \file_get_contents($sessionFile) ?: '';
        }

        return '';
    }

    /**
     * @param string $id
     * @param string $data
     */
    #[\ReturnTypeWillChange]
    public function write($id, $data): bool
    {
        return (bool) \file_put_contents($this->getSessionFile($id), $data, LOCK_EX);
    }

    /**
     * @throws \Exception
     */
    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        // 垃圾回收
        if (\random_int(1, $this->gcDivisor) <= $this->gcProbability) {
            $this->gc($this->gcMaxLifetime);
        }
        return true;
    }

    /**
     * @param string $id
     */
    #[\ReturnTypeWillChange]
    public function destroy($id): bool
    {
        return $this->unlink($this->getSessionFile($id));
    }

    /**
     * 打开session.
     */
    #[\ReturnTypeWillChange]
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * 查找文件.
     */
    protected function findFiles(string $root, \Closure $filter): \Generator
    {
        $items = new \FilesystemIterator($root);

        /** @var \SplFileInfo $item */
        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                yield from $this->findFiles($item->getPathname(), $filter);
            } elseif ($filter($item)) {
                yield $item;
            }
        }
    }

    /**
     * 生成session文件名.
     */
    protected function getSessionFile(string $id): string
    {
        return \rtrim($this->path, '/\\') . '/sess_' . $id;
    }

    /**
     * 判断文件是否存在后，删除.
     */
    private function unlink(string $file): bool
    {
        return \is_file($file) && \unlink($file);
    }
}
