<?php

namespace Larashed\Agent\Errors;

use SplFileObject;

class File
{
    private $file;

    public function __construct($path)
    {
        $this->file = new SplFileObject($path);
    }

    public function numberOfLines()
    {
        $this->file->seek(PHP_INT_MAX);

        return $this->file->key() + 1;
    }

    public function getLine($lineNumber = null)
    {
        if (is_null($lineNumber)) {
            return $this->getNextLine();
        }

        $this->file->seek($lineNumber - 1);

        return $this->file->current();
    }

    public function getNextLine()
    {
        $this->file->next();

        return $this->file->current();
    }
}
