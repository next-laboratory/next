<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils;

use FilesystemIterator;
use Max\Macro\Macroable;
use Max\Utils\Exception\FileNotFoundException;
use RuntimeException;
use SplFileObject;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Mime\MimeTypes;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
class Filesystem
{
    use Macroable;

    /**
     * Determine if a file or directory exists.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Determine if a file or directory is missing.
     */
    public function missing(string $path): bool
    {
        return !static::exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @throws FileNotFoundException
     */
    public function get(string $path, bool $lock = false): string
    {
        if (static::isFile($path)) {
            return $lock ? static::sharedGet($path) : file_get_contents($path);
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }

    /**
     * Get contents of a file with shared access.
     */
    public function sharedGet(string $path): string
    {
        $contents = '';
        $handle   = fopen($path, 'rb');
        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);
                    $contents = fread($handle, static::size($path) ?: 1);
                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * Get the returned value of a file.
     *
     * @throws FileNotFoundException
     */
    public function getRequire(string $path, array $data = []): mixed
    {
        if (static::isFile($path)) {
            $__path = $path;
            $__data = $data;

            return (static function() use ($__path, $__data) {
                extract($__data, EXTR_SKIP);

                return require $__path;
            })();
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }

    /**
     * Require the given file once.
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function requireOnce(string $path, array $data = [])
    {
        if (static::isFile($path)) {
            $__path = $path;
            $__data = $data;

            return (static function() use ($__path, $__data) {
                extract($__data, EXTR_SKIP);

                return require_once $__path;
            })();
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }

    /**
     * Get the contents of a file one line at a time.
     *
     * @throws FileNotFoundException
     */
    public function lines(string $path): LazyCollection
    {
        if (!static::isFile($path)) {
            throw new FileNotFoundException(
                "File does not exist at path {$path}."
            );
        }

        return LazyCollection::make(function() use ($path) {
            $file = new SplFileObject($path);

            $file->setFlags(SplFileObject::DROP_NEW_LINE);

            while (!$file->eof()) {
                yield $file->fgets();
            }
        });
    }

    /**
     * Get the MD5 hash of the file at the given path.
     */
    public function hash(string $path): string
    {
        return md5_file($path);
    }

    /**
     * Write the contents of a file.
     */
    public function put(string $path, string $contents, bool $lock = false): bool|int
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Write the contents of a file, replacing it atomically if it already exists.
     */
    public function replace(string $path, string $content): void
    {
        // If the path already exists and is a symlink, get the real path...
        clearstatcache(true, $path);

        $path     = realpath($path) ?: $path;
        $tempPath = tempnam(dirname($path), basename($path));

        // Fix permissions of tempPath because `tempnam()` creates it with permissions set to 0600...
        chmod($tempPath, 0777 - umask());
        file_put_contents($tempPath, $content);
        rename($tempPath, $path);
    }

    /**
     * Replace a given string within a given file.
     */
    public function replaceInFile(array|string $search, array|string $replace, string $path): void
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Prepend to a file.
     *
     * @throws FileNotFoundException
     */
    public function prepend(string $path, string $data): bool|int
    {
        if (static::exists($path)) {
            return static::put($path, $data . static::get($path));
        }

        return static::put($path, $data);
    }

    /**
     * Append to a file.
     */
    public function append(string $path, string $data): bool|int
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Get or set UNIX mode of a file or directory.
     */
    public function chmod(string $path, ?int $mode = null): bool|string
    {
        if ($mode) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Delete the file at a given path.
     */
    public function delete(array|string $paths): bool
    {
        $paths   = is_array($paths) ? $paths : func_get_args();
        $success = true;

        foreach ($paths as $path) {
            if (!@unlink($path)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Move a file to a new location.
     */
    public function move(string $path, string $target): bool
    {
        return rename($path, $target);
    }

    /**
     * Copy a file to a new location.
     */
    public function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }

    /**
     * Create a symlink to the target file or directory. On Windows, a hard link is created if the target is a file.
     */
    public function link(string $target, string $link): bool
    {
        if (!windows_os()) {
            return symlink($target, $link);
        }

        $mode = static::isDirectory($target) ? 'J' : 'H';

        return (bool)exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
    }

    /**
     * Create a relative symlink to the target file or directory.
     *
     * @throws RuntimeException
     */
    public function relativeLink(string $target, string $link): void
    {
        if (!class_exists(SymfonyFilesystem::class)) {
            throw new RuntimeException(
                'To enable support for relative links, please install the symfony/filesystem package.'
            );
        }

        $relativeTarget = (new SymfonyFilesystem())->makePathRelative($target, dirname($link));

        static::link($relativeTarget, $link);
    }

    /**
     * Extract the file name from a file path.
     */
    public function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     */
    public function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     */
    public function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     */
    public function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Guess the file extension from the mime-type of a given file.
     *
     * @throws RuntimeException
     */
    public function guessExtension(string $path): ?string
    {
        if (!class_exists(MimeTypes::class)) {
            throw new RuntimeException(
                'To enable support for guessing extensions, please install the symfony/mime package.'
            );
        }

        return (new MimeTypes())->getExtensions(static::mimeType($path))[0] ?? null;
    }

    /**
     * Get the file type of given file.
     */
    public function type(string $path): string
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     */
    public function mimeType(string $path): bool|string
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the file size of a given file.
     */
    public function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     */
    public function lastModified(string $path): int
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     */
    public function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is readable.
     */
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * Determine if the given path is writable.
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     */
    public function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * Find path names matching a given pattern.
     */
    public function glob(string $pattern, int $flags = 0): bool|array
    {
        return glob($pattern, $flags);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @return SplFileInfo[]
     */
    public function files(string|array $directory, bool $hidden = false, string $pattern = '*'): array
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->name($pattern)->depth(0)->sortByName(),
            false
        );
    }

    /**
     * Get all the files from the given directory (recursive).
     *
     * @return SplFileInfo[]
     */
    public function allFiles(string $directory, bool $hidden = false): array
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->sortByName(),
            false
        );
    }

    /**
     * Get all the directories within a given directory.
     */
    public function directories(string $directory): array
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * Ensure a directory exists.
     */
    public function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        if (!static::isDirectory($path)) {
            static::makeDirectory($path, $mode, $recursive);
        }
    }

    /**
     * Create a directory.
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Move a directory.
     */
    public function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        if ($overwrite && static::isDirectory($to) && !static::deleteDirectory($to)) {
            return false;
        }

        return @rename($from, $to) === true;
    }

    /**
     * Copy a directory from one location to another.
     */
    public function copyDirectory(string $directory, string $destination, ?int $options = null): bool
    {
        if (!static::isDirectory($directory)) {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        static::ensureDirectoryExists($destination, 0777);

        $items = new FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            // As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (!static::copyDirectory($path, $target, $options)) {
                    return false;
                }
            }

            // If the current items is just a regular file, we will just copy this to the new
            // location and keep looping. If for some reason the copy fails we'll bail out
            // and return false, so the developer is aware that the copy process failed.
            else {
                if (!static::copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively delete a directory.
     * The directory itself may be optionally preserved.
     */
    public function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        if (!static::isDirectory($directory)) {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that subdirectory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() && !$item->isLink()) {
                static::deleteDirectory($item->getPathname());
            }

            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all the files in this directory
            // and calling directories recursively, so we delete the real path.
            else {
                static::delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Remove all the directories within a given directory.
     */
    public function deleteDirectories(string $directory): bool
    {
        $allDirectories = static::directories($directory);

        if (!empty($allDirectories)) {
            foreach ($allDirectories as $directoryName) {
                static::deleteDirectory($directoryName);
            }

            return true;
        }

        return false;
    }

    /**
     * Empty the specified directory of all files and folders.
     */
    public function cleanDirectory(string $directory): bool
    {
        return static::deleteDirectory($directory, true);
    }
}
