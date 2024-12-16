<?php

namespace Michalsn\CodeIgniterTranslatable\Commands\Support;

use CodeIgniter\Files\File;
use RuntimeException;

class MigrationManager
{
    private readonly string $migrationPath;

    public function __construct(?string $path = null)
    {
        $this->migrationPath = $path ?? APPPATH . 'Database/Migrations/';
    }

    /**
     * Get the latest migration file.
     */
    public function getLatestMigration(): ?File
    {
        if (! is_dir($this->migrationPath)) {
            throw new RuntimeException("Invalid directory: {$this->migrationPath}");
        }

        $files = [];

        foreach (scandir($this->migrationPath) as $file) {
            $filePath = $this->migrationPath . $file;
            if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
                $files[] = new File($filePath);
            }
        }

        // Sort files by name
        usort($files, static fn (File $a, File $b) => strcmp($a->getBasename(), $b->getBasename()));

        // Return the last file (the latest migration)
        return end($files) ?: null;
    }
}
