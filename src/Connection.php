<?php

declare(strict_types=1);

namespace AttachmentDownloader;

class Connection
{
    private $connection;
    private string $email;

    public function __construct($connection, string $email)
    {
        $this->connection = $connection;
        $this->email      = $email;
    }

    public function get()
    {
        return $this->connection;
    }

    public function email(): string
    {
        return $this->email;
    }
}
