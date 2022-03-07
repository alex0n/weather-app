<?php
declare(strict_types=1);

namespace App\Service;

use Generator;

class CsvFileReader
{
    protected $file;

    public function __construct($fileResource = null)
    {
        $this->file = $fileResource;
    }

    public function open(string $filePath): self
    {
        $file = fopen($filePath, 'rb');
        return new self($file);
    }

    public function rows(): Generator
    {
        $separator = $this->detectSeparator();
        while (!feof($this->file)) {
            yield fgetcsv($this->file, 4096, $separator);
        }
    }

    public function close(): void
    {
        if ($this->file !== null) {
            fclose($this->file);
        }
    }

    private function detectSeparator(): string
    {
        $delimiters = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];

        $firstLine = fgets($this->file);

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        $delimiter = array_search(max($delimiters), $delimiters, true);

        return $delimiter ?: ',';
    }
}
