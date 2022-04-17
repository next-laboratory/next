<?php

namespace Max\WebSocket;

class Message
{
    protected array $to;

    public function __construct(protected mixed $message, $to, protected $from = null)
    {
        $this->to = array($to);
    }
}