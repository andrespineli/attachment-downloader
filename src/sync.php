<?php

declare(strict_types=1);

require_once "Gmail.php";
require_once "Inbox.php";

use AttachmentDownloader\Gmail;
use AttachmentDownloader\Inbox;

$configs = json_decode(file_get_contents('./config.json'), true);

foreach ($configs as $key => $value) {
    if ($key !== 'accounts') {
        $_ENV[$key] = $value;
    }
}

//while (true) {
    foreach ($configs['accounts'] as $account) {
        $connection = Gmail::connect($account['email'], $account['password']);
        $inbox = new Inbox($connection);
        $emails = $inbox->search();
        $attachments = $emails->downloadAttachments();
    }
//}
