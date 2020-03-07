<?php

declare(strict_types=1);

require "./vendor/autoload.php";

use AttachmentDownloader\Gmail;
use AttachmentDownloader\Inbox;

$config = json_decode(file_get_contents('./config.json'), true);
$_ENV['extensions'] = $config['extensions'];

$connection = Gmail::connect($config['emails'][0]['email'], $config['emails'][0]['password']);

$inbox = new Inbox($connection);

$emails = $inbox->search();

$attachments = $emails->downloadAttachments();
