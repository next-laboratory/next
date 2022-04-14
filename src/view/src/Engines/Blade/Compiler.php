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

namespace Max\View\Engines\Blade;

use Exception;
use Max\View\Engines\Blade;
use Max\View\Exceptions\ViewNotExistException;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function md5;
use function mkdir;
use function sprintf;
use function str_replace;
use function trim;

class Compiler
{
    /**
     * @var array
     */
    protected array $sections = [];

    /**
     * @var string|null
     */
    protected ?string $parent;

    /**
     * @var Blade
     */
    protected Blade $blade;

    /**
     * Compiler constructor.
     *
     * @param Blade $blade
     */
    public function __construct(Blade $blade)
    {
        $this->blade = $blade;
    }

    /**
     * 读模板
     *
     * @param $template
     *
     * @return false|string
     * @throws ViewNotExistException
     */
    protected function readFile($template): bool|string
    {
        if (file_exists($template)) {
            if ($content = file_get_contents($template)) {
                return $content;
            }
        }
        throw new ViewNotExistException('View ' . $template . ' does not exist');
    }

    /**
     * @param $template
     *
     * @return string
     */
    protected function getRealPath($template): string
    {
        return sprintf('%s%s%s',
            $this->blade->getPath(),
            str_replace('.', '/', $template),
            $this->blade->getSuffix()
        );
    }

    /**
     * 编译
     *
     * @param $template
     *
     * @return string
     * @throws ViewNotExistException
     */
    public function compile($template): string
    {
        $compileDir   = $this->blade->getCompileDir();
        $compiledFile = $compileDir . md5($template) . '.php';

        if (false === $this->blade->isCache() || false === file_exists($compiledFile)) {
            !is_dir($compileDir) && mkdir($compileDir, 0755, true);
            $stream = $this->compileView($template);
            while (isset($this->parent)) {
                $parent       = $this->parent;
                $this->parent = null;
                $stream       = $this->compileView($parent);
            }
            file_put_contents($compiledFile, $stream, LOCK_EX);
        }

        return $compiledFile;
    }

    /**
     * 编译文件
     *
     * @param string $file
     *
     * @return string
     * @throws ViewNotExistException
     */
    protected function compileView(string $file): string
    {
        return preg_replace_callback_array([
            '/@(.*?)\((.*)?\)/'                                                        => [$this, 'compileFunc'],
            '/\{!!([\s\S]*?)!!\}/'                                                     => [$this, 'compileRaw'],
            '/\{\{((--)?)([\s\S]*?)\\1\}\}/'                                           => [$this, 'compileEchos'],
            '/@(section|switch)\((.*?)\)([\s\S]*?)@end\\1/'                            => [$this, 'compileParcel'],
            '/@(php|else|endphp|endforeach|endfor|endif|endunless|endempty|endisset)/' => [$this, 'compileDirective']
        ], $this->readFile($this->getRealPath($file)));
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    protected function compileRaw(array $matches): string
    {
        return sprintf('<?php echo %s; ?>', $matches[1]);
    }

    /**
     * 编译输出内容
     *
     * @param array $matches
     *
     * @return string
     */
    protected function compileEchos(array $matches)
    {
        if ('' === $matches[1]) {
            return sprintf('<?php echo htmlspecialchars((string)%s, ENT_QUOTES); ?>', $matches[3]);
        }
    }

    /**
     * 编译包裹内容
     *
     * @param array $matches
     *
     * @return string
     */
    protected function compileParcel(array $matches)
    {
        [$directive, $condition, $segment] = array_slice($matches, 1);
        switch ($directive) {
            case 'section':
                $this->sections[$this->trim($condition)] = $segment;
                break;
            case 'switch':
                $segment = preg_replace(
                    ['/@case\((.*)\)/', '/@default/',],
                    ["<?php case \\1: ?>", '<?php default: ?>',],
                    $segment
                );
                return sprintf('<?php switch(%s): ?>%s<?php endswitch; ?>', $condition, trim($segment));
        }
    }

    /**
     * 编译指令
     *
     * @param array $matches
     *
     * @return string
     */
    protected function compileDirective(array $matches): string
    {
        return match ($directive = $matches[1]) {
            'php' => '<?php ',
            'endphp' => '?>',
            'else' => '<?php else: ?>',
            'endisset', 'endunless', 'endempty' => '<?php endif; ?>',
            default => sprintf('<?php %s; ?>', $directive),
        };
    }

    /**
     * 编译函数
     *
     * @param array $matches
     *
     * @return mixed|string|void
     * @throws ViewNotExistException
     */
    protected function compileFunc(array $matches)
    {
        [$func, $arguments] = [$matches[1], $this->trim($matches[2])];
        switch ($func) {
            case 'yield':
                $value = array_map([$this, 'trim'], explode(',', $arguments, 2));
                return $this->sections[$value[0]] ?? ($value[1] ?? '');
            case 'extends':
                $this->parent = $arguments;
                break;
            case 'include':
                return $this->compileView($arguments);
            case 'if':
            case 'elseif':
                return sprintf('<?php %s (%s): ?>', $func, $arguments);
            case 'unless':
                return sprintf('<?php if (!(%s)): ?>', $arguments);
            case 'empty':
            case 'isset':
                return sprintf('<?php if (%s(%s)): ?>', $func, $arguments);
            case 'for':
            case 'foreach':
                return sprintf('<?php %s(%s): ?>', $func, $arguments);
            default:
                return $matches[0];
        }
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function trim(string $value): string
    {
        return trim($value, '\'" ');
    }
}
