<?php

declare(strict_types=1);

namespace AttachmentDownloader;

require_once "Connection.php";
require_once "Emails.php";

class Inbox
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function search(string $criteria = 'ALL'): Emails
    {
        $data = imap_search($this->connection->get(), $criteria);
        return new Emails($data, $this->connection);
    }
}
