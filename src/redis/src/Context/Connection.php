<?php

namespace Max\Redis\Context;

class Connection extends \ArrayObject
{
    public function __destruct()
    {
        foreach ($this->getIterator() as $item) {
            $item['pool']->put($item['item']);
        }
    }
}
