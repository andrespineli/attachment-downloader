<?php

declare(strict_types=1);

namespace AttachmentDownloader;

class Collection implements \Iterator
{
    private $position = 0;
    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->position = 0;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    public function add($value)
    {
        $this->data[] = $value;
    }

    public function rsort()
    {
        rsort($this->data);
    }
}
