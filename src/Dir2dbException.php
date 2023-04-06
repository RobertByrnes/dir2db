<?php

namespace dir2db;

use RuntimeException;
use Throwable;

class Dir2dbException extends RuntimeException
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        // die(var_dump($message, $code, $previous));
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message} in {$this->file}({$this->line})\n";
    }
}
