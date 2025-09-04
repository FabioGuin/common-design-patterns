<?php

namespace App\Models;

use App\Traits\Poolable;
use Illuminate\Support\Facades\Log;

class FileConnection
{
    use Poolable;

    private string $filePath;
    private $fileHandle;
    private bool $isOpen = false;
    private string $mode;

    public function __construct(string $filePath, string $mode = 'r')
    {
        $this->filePath = $filePath;
        $this->mode = $mode;
        $this->open();
    }

    private function open(): void
    {
        try {
            $this->fileHandle = fopen($this->filePath, $this->mode);
            if ($this->fileHandle === false) {
                throw new \Exception("Failed to open file: {$this->filePath}");
            }
            
            $this->isOpen = true;
            Log::info("File connection opened: {$this->filePath}");
            
        } catch (\Exception $e) {
            Log::error("Failed to open file {$this->filePath}: " . $e->getMessage());
            throw new \Exception("File connection failed: " . $e->getMessage());
        }
    }

    public function read(int $length = 1024): string
    {
        if (!$this->isOpen) {
            throw new \Exception("File connection is not open");
        }

        $data = fread($this->fileHandle, $length);
        if ($data === false) {
            throw new \Exception("Failed to read from file");
        }

        return $data;
    }

    public function write(string $data): int
    {
        if (!$this->isOpen) {
            throw new \Exception("File connection is not open");
        }

        $bytes = fwrite($this->fileHandle, $data);
        if ($bytes === false) {
            throw new \Exception("Failed to write to file");
        }

        return $bytes;
    }

    public function readLine(): string
    {
        if (!$this->isOpen) {
            throw new \Exception("File connection is not open");
        }

        $line = fgets($this->fileHandle);
        if ($line === false) {
            throw new \Exception("Failed to read line from file");
        }

        return $line;
    }

    public function writeLine(string $line): int
    {
        return $this->write($line . "\n");
    }

    public function seek(int $offset, int $whence = SEEK_SET): bool
    {
        if (!$this->isOpen) {
            throw new \Exception("File connection is not open");
        }

        return fseek($this->fileHandle, $offset, $whence) === 0;
    }

    public function tell(): int
    {
        if (!$this->isOpen) {
            throw new \Exception("File connection is not open");
        }

        $position = ftell($this->fileHandle);
        if ($position === false) {
            throw new \Exception("Failed to get file position");
        }

        return $position;
    }

    public function eof(): bool
    {
        if (!$this->isOpen) {
            throw new \Exception("File connection is not open");
        }

        return feof($this->fileHandle);
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    protected function onRelease(): void
    {
        // Reset della posizione del file
        if ($this->isOpen) {
            $this->seek(0);
        }
    }

    protected function onReset(): void
    {
        // Chiudi e riapri il file
        if ($this->isOpen) {
            fclose($this->fileHandle);
            $this->isOpen = false;
        }
        
        $this->open();
    }

    public function __destruct()
    {
        if ($this->isOpen && $this->fileHandle) {
            fclose($this->fileHandle);
            $this->isOpen = false;
        }
    }
}
