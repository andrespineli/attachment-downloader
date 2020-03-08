<?php

declare(strict_types=1);

namespace AttachmentDownloader;

class Emails extends Collection
{
    private $decode;

    private Connection $connection;

    public function __construct(array $data, Connection $connection)
    {
        parent::__construct($data);
        $this->connection = $connection;
        $this->decode = [
            3 => function ($file) {
                return base64_decode($file);
            },
            4 => function ($file) {
                return quoted_printable_decode($file);
            }
        ];
    }

    public function downloadAttachments(): void
    {
        $this->createDirectoryIfNotExists($this->connection->email());
        $this->newestOrder();
        $this->fetchStructure();
    }

    private function createDirectoryIfNotExists($name): bool
    {
        $path = "{$_ENV['folder']}/{$name}";

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
                $this->setAttachments($part->dparameters, $part->encoding, $email, $partNumber);
            }
        }
    }

    private function setAttachments($parameters, $encoding, $email, $partNumber)
    {
        foreach ($parameters as $parameter) {
            if ($parameter->attribute === 'FILENAME') {

                $file = $this->fetchBody($email, $partNumber);
                $file = $this->decode($file, $encoding);

                if (!$file) {
                    echo "Skipping... {$this->connection->email()} | {$parameter->value}" . PHP_EOL;
                    continue;
                }

                if (!$this->downloadbleExtension($parameter->value)) {
                    echo "Skipping... {$this->connection->email()} | {$parameter->value}" . PHP_EOL;
                    continue;
                }

                $this->saveFile($parameter->value, $file);
            }
        }
    }

    private function fetchBody($email, $partNumber)
    {
        $section = (string) ($partNumber + 1);
        return imap_fetchbody($this->connection->get(), $email, $section);
    }

    private function decode($file, $encoding)
    {
        if (isset($this->decode[$encoding])) {
            return $this->decode[$encoding]($file);
        }

        return false;
    }

    private function downloadbleExtension($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $extensions = str_replace(' ', '', $_ENV['extensions']);

        if (!in_array($ext, explode(',', $extensions))) {
            return false;
        }

        return true;
    }

    private function saveFile($name, $file)
    {
        $path = "{$_ENV['folder']}/{$this->connection->email()}/{$name}";

        if (!file_exists($path)) {
            echo "Downloading... {$this->connection->email()} | {$name}" . PHP_EOL;
            file_put_contents($path, $file);
        }
    }
}
