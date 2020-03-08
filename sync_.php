<?php

require "./vendor/autoload.php";

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'user@gmail.com';
$password = '********';

$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

$emails = imap_search($inbox, 'ALL');

rsort($emails);

$attachments = [];

foreach ($emails as $email) {
    $structure = imap_fetchstructure($inbox, $email);

    if (!isset($structure->parts)) {
        continue;
    }

    foreach ($structure->parts as $partNumber => $part) {
        if ($part->ifdparameters) {
            foreach ($part->dparameters as $parameter) {
                if ($parameter->attribute === 'FILENAME') {

                    $attachment = [
                        'file_name' => $parameter->value,
                        'email_number' => $email
                    ];

                    $attachment['file'] = imap_fetchbody($inbox, $email, $partNumber + 1);

                    if ($part->encoding === 3) {
                        $attachment['file'] = base64_decode($attachment['file']);
                    }

                    if ($part->encoding === 4) {
                        $attachment['file'] = quoted_printable_decode($attachment['file']);
                    }

                    file_put_contents("./attachments/{$attachment['file_name']}", $attachment['file']);
                }
            }
        }
    }
}