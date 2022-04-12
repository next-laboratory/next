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

namespace Max\Database\Pools;

use Max\Config\Repository;
use PDO;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool as SwoolePDOPool;
use Swoole\Exception;

class PDOPool
{
    /**
     * @var SwoolePDOPool[]
     */
    protected static array $pool = [];

    /**
     * 默认配置
     */
    protected const DEFAULT_PDO_OPTIONS = [
        'driver'     => 'mysql',
        'host'       => '127.0.0.1',
        'user'       => 'root',
        'password'   => 'root',
        'unixSocket' => null,
        'database'   => '',
        'port'       => 3306,
        'options'    => [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_CASE       => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            //        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            //        PDO::ATTR_STRINGIFY_FETCHES => false,
            //        PDO::ATTR_EMULATE_PREPARES  => false,
        ],
        'charset'    => 'utf8mb4',
        'poolSize'   => 64,
    ];

    /**
     * @param array $config
     */
    public function __construct(protected array $config)
    {
    }

    public static function __new(Repository $repository)
    {
        return new static($repository->get('database'));
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPool(string $name): bool
    {
        return isset(static::$pool[$name]);
    }

    /**
     * @param string|null $name
     *
     * @return SwoolePDOPool
     * @throws Exception
     */
    public function getPool(?string $name): SwoolePDOPool
    {
        $name ??= $this->config['default'];
        if (!$this->hasPool($name)) {
            if (!isset($this->config['connections'][$name])) {
                throw new Exception('配置不存在');
            }
            $config              = array_replace_recursive(self::DEFAULT_PDO_OPTIONS, $this->config['connections'][$name] ?? []);
            static::$pool[$name] = new SwoolePDOPool((new PDOConfig())
                ->withDriver($config['driver'])
                ->withHost($config['host'])
                ->withPort($config['port'])
                ->withUnixSocket($config['unixSocket'])
                ->withOptions($config['options'])
                ->withDbname($config['database'])
                ->withCharset($config['charset'])
                ->withUsername($config['user'])
                ->withPassword($config['password']));
        }

        return static::$pool[$name];
    }
}
