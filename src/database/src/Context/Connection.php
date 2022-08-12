<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Context;

use ArrayObject;
use Max\Database\Connector\PoolConnector;
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
                if (! $PDO->query('SELECT 1')) {
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
