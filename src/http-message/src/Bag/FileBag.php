<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message\Bag;

use Max\Http\Message\Stream\FileStream;
use Max\Http\Message\UploadedFile;

class FileBag
{
    public function __construct(
        protected array $uploadedFiles = []
    ) {
    }

    public static function createFromGlobal(): static
    {
        $bag = new static();
        foreach ($_FILES as $key => $file) {
            $bag->convertToUploadedFiles($bag->uploadedFiles, $key, $file['name'], $file['tmp_name'], $file['type'], $file['size'], $file['error']);
        }
        return $bag;
    }

    public function all(): array
    {
        return $this->uploadedFiles;
    }

    protected function convertToUploadedFiles(&$uploadedFiles, $k, $name, $tmpName, $type, $size, $error): void
    {
        if (is_string($name)) {
            $uploadedFiles[$k] = new UploadedFile($error > 0 ? null : new FileStream($tmpName), $size, $name, $type, $error);
        } else {
            foreach ($name as $key => $value) {
                $this->convertToUploadedFiles($uploadedFiles[$k], $key, $value, $tmpName[$key], $type[$key], $size[$key], $error[$key]);
            }
        }
    }
}
