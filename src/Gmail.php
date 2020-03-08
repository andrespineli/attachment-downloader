<?php

declare(strict_types=1);

namespace AttachmentDownloader;

require_once "Connection.php";

class Gmail
{
    const HOST = '{imap.gmail.com:993/imap/ssl}INBOX';

    public static function connect(string $email, string $password): Connection
    {
        $resource = imap_open(Gmail::HOST, $email, $password);

        if (!$resource) {
            $errorMessage = imap_last_error();
            throw new \Exception("Cannot connect to Gmail: {$errorMessage}", 1);
        }

        return new Connection($resource, $email);
    }
}
