<?php

declare(strict_types=1);

namespace AttachmentDownloader;

class Emails extends Collection
{
    private Connection $connection;

    public function __construct(array $data, Connection $connection)
    {
        parent::__construct($data);
        $this->connection = $connection;
    }

    public function downloadAttachments(): void
    {
        $this->createDirectoryIfNotExists($this->connection->email());

        $this->newestOrder();

        $this->fetchStructure();
    }

    private function createDirectoryIfNotExists($name): bool
    {
        $path = "./attachments/{$name}";

        if (!file_exists($path)) {
            return mkdir($path, 0777, true);
        }

        return true;
    }

    private function newestOrder(): void
    {
        $this->rsort();
    }

    private function fetchStructure()
    {
        foreach ($this->data as $email) {
            $structure = imap_fetchstructure($this->connection->get(), $email);

            if (!isset($structure->parts)) {
                continue;
            }

            $this->extractParts($structure, $email);
        }
    }

    private function extractParts($structure, $email)
    {
        foreach ($structure->parts as $partNumber => $part) {
            if ($part->ifdparameters) {
                $this->setAttachments($part, $email, $partNumber);
            }
        }
    }

    private function setAttachments($part, $email, $partNumber)
    {
        $decode = [
            3 => function ($file) {
                return base64_decode($file);
            },
            4 => function ($file) {
                return quoted_printable_decode($file);
            }
        ];

        foreach ($part->dparameters as $parameter) {
            if ($parameter->attribute === 'FILENAME') {

                $section = (string) ($partNumber + 1);

                $file = imap_fetchbody($this->connection->get(), $email, $section);

                if (!isset($decode[$part->encoding])) {
                    echo "Skipping... {$this->connection->email()} | {$parameter->value}" . PHP_EOL;
                    continue;
                }

                $file = $decode[$part->encoding]($file);

                $ext = pathinfo($parameter->value, PATHINFO_EXTENSION);

                $extensions = str_replace(' ', '', $_ENV['extensions']);

                if (!in_array($ext, explode(',', $extensions))) {
                    echo "Skipping... {$this->connection->email()} | {$parameter->value}" . PHP_EOL;
                    continue;
                }

                echo "Downloading... {$this->connection->email()} | {$parameter->value}" . PHP_EOL;

                file_put_contents("./attachments/{$this->connection->email()}/{$parameter->value}", $file);
            }
        }
    }
}
