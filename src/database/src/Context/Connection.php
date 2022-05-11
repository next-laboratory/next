<?php

namespace Max\Database\Context;

use ArrayObject;

class Connection extends ArrayObject
{
    public function __destruct()
    {
        foreach ($this->getIterator() as $item) {
            $item['pool']->put($item['item']);
        }
    }
}
