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

namespace Max\Redis\Context;

use ArrayObject;
use Max\Redis\Redis;
use RedisException;

class Connection extends ArrayObject
{
    public function __destruct()
    {
        foreach ($this->getIterator() as $item) {
            $pool = $item['pool'];
            /** @var Redis $redis */
            $redis = $item['item'];
            try {
                if (!$redis->ping()) {
                    $redis = null;
                }
            } catch (RedisException) {
                $redis = null;
            } finally {
                $pool->put($redis);
            }
        }
    }
}
