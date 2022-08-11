<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\View\Engine\Blade;

use Max\View\Engine\Blade;
use Max\View\Exception\ViewNotExistException;

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
    protected array $sections = [];

    protected ?string $parent;

    protected Blade $blade;

    /**
     * Compiler constructor.
     */
    public function __construct(Blade $blade)
    {
        $this->blade = $blade;
    }

    /**
     * 编译.
     * @param mixed $template
     */
    public function compile($template): string
    {
        $compileDir   = $this->blade->getCompileDir();
        $compiledFile = $compileDir . md5($template) . '.php';

        if ($this->blade->isCache() === false || file_exists($compiledFile) === false) {
            ! is_dir($compileDir) && mkdir($compileDir, 0755, true);
            $content = $this->compileView($template);
            while (isset($this->parent)) {
                $parent       = $this->parent;
                $this->parent = null;
                $content      = $this->compileView($parent);
            }
            file_put_contents($compiledFile, $content, LOCK_EX);
        }

        return $compiledFile;
    }

    /**
     * 读模板
     *
     * @throws ViewNotExistException
     */
    protected function readFile(string $template): bool|string
    {
        if (file_exists($template) && (false !== ($content = file_get_contents($template)))) {
            return $content;
        }
        throw new ViewNotExistException('View ' . $template . ' does not exist');
    }

    protected function getRealPath(string $template): string
    {
        return sprintf(
            '%s%s%s',
            $this->blade->getPath(),
            str_replace('.', '/', $template),
            $this->blade->getSuffix()
        );
    }

    /**
     * 编译文件.
     *
     * @throws ViewNotExistException
     */
    protected function compileView(string $file): string
    {
        return preg_replace_callback_array([
            '/@(.*?)\((.*)?\)/'                                                        => [$this, 'compileFunc'],
            '/\{!!([\s\S]*?)!!\}/'                                                     => [$this, 'compileRaw'],
            '/\{\{((--)?)([\s\S]*?)\\1\}\}/'                                           => [$this, 'compileEchos'],
            '/@(section|switch)\((.*?)\)([\s\S]*?)@end\\1/'                            => [$this, 'compileParcel'],
            '/@(php|else|endphp|endforeach|endfor|endif|endunless|endempty|endisset)/' => [$this, 'compileDirective'],
        ], $this->readFile($this->getRealPath($file)));
    }

    protected function compileRaw(array $matches): string
    {
        return sprintf('<?php echo %s; ?>', $matches[1]);
    }

    /**
     * 编译输出内容.
     *
     * @return string
     */
    protected function compileEchos(array $matches)
    {
        if ($matches[1] === '') {
            return sprintf('<?php echo htmlspecialchars((string)%s, ENT_QUOTES); ?>', $matches[3]);
        }
    }

    /**
     * 编译包裹内容.
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
                    ['/@case\((.*)\)/', '/@default/'],
                    ['<?php case \\1: ?>', '<?php default: ?>'],
                    $segment
                );
                return sprintf('<?php switch(%s): ?>%s<?php endswitch; ?>', $condition, trim($segment));
        }
    }

    /**
     * 编译指令.
     */
    protected function compileDirective(array $matches): string
    {
        return match ($directive = $matches[1]) {
            'php'    => '<?php ',
            'endphp' => '?>',
            'else'   => '<?php else: ?>',
            'endisset', 'endunless', 'endempty' => '<?php endif; ?>',
            default => sprintf('<?php %s; ?>', $directive),
        };
    }

    /**
     * 编译函数.
     *
     * @throws ViewNotExistException
     * @return mixed|string|void
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

    protected function trim(string $value): string
    {
        return trim($value, '\'" ');
    }
}
