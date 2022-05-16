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

namespace Max\Database\Context;

use ArrayObject;
use Max\Database\Connectors\PoolConnector;
use Throwable;

class Connection extends ArrayObject
{
    public function __destruct()
    {
        foreach ($this->getIterator() as $item) {
            /** @var PoolConnector $pool */
            $pool = $item['pool'];
            /** @var \PDO $PDO */
            $PDO = $item['item'];
            try {
                if (!$PDO->query('SELECT 1')) {
                    $PDO = null;
                }
            } catch (Throwable) {
                $PDO = null;
            } finally {
                $pool->put($PDO);
            }
        }
    }
}
