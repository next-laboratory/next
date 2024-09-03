<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\VarDumper;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

trait DumperHandler
{
    public static function convertToHtml(Dumper $abort): string
    {
        ob_start();
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
        foreach ($abort->vars as $var) {
            (new HtmlDumper())->dump($cloner->cloneVar($var));
        }
        return (string) ob_get_clean();
    }
}
