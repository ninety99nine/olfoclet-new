<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class CollateProjectContext extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'code:collate
                            {path=all : The relative path to the folder or "all" for entire project}
                            {--exclude-files= : Comma-separated list of filenames or patterns to exclude}
                            {--exclude-dirs= : Comma-separated list of directory names to exclude}
                            {--limit=50000 : Character limit per chunk}';

    /**
     * The console command description.
     */
    protected $description = 'Collates project files into character-limited chunks for AI context';

    /**
     * Hardcoded exclusions to keep the AI context clean.
     * Added .sqlite and other database binaries to exts.
     */
    protected array $hardExclusions = [
        'dirs' => ['node_modules', 'vendor', 'storage', 'bootstrap/cache', '.git', 'public/build', 'prompt-context'],
        'exts' => [
            '*.png', '*.jpg', '*.jpeg', '*.gif', '*.svg', '*.ico', '*.lock',
            '*.pdf', '*.zip', '*.tar', '*.gz', '*.sqlite', '*.sqlite-journal',
            '*.db', '*.pro'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputPath = $this->argument('path');
        $limit = (int) $this->option('limit');

        // Parse custom exclusions
        $customFiles = $this->option('exclude-files') ? explode(',', $this->option('exclude-files')) : [];
        $customDirs = $this->option('exclude-dirs') ? explode(',', $this->option('exclude-dirs')) : [];

        // Determine target path
        $targetPath = $inputPath === 'all' ? base_path() : base_path(ltrim($inputPath, '/'));

        if (!File::isDirectory($targetPath)) {
            $this->error("Directory {$targetPath} not found.");
            return 1;
        }

        $customPrompt = $this->ask('Enter the custom prompt/instructions for the final chunk');

        // Setup the output directory
        $outputDir = base_path('prompt-context');
        if (File::exists($outputDir)) {
            File::deleteDirectory($outputDir);
        }
        File::makeDirectory($outputDir);

        $finder = new Finder();
        $finder->files()
            ->in($targetPath)
            ->exclude(array_merge($this->hardExclusions['dirs'], $customDirs))
            ->notName($this->hardExclusions['exts'])
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);

        // Apply custom file exclusions
        foreach ($customFiles as $pattern) {
            $pattern = trim($pattern);
            if (!empty($pattern)) {
                $finder->notName($pattern);
            }
        }

        $currentChunkContent = "";
        $chunkIndex = 1;

        foreach ($finder as $file) {
            // Skip files over 500kb to prevent memory issues and AI bloat
            if ($file->getSize() > 1024 * 500) {
                continue;
            }

            $realPath = $file->getRealPath();
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $realPath);

            // Strict safety check for the output directory
            if (str_contains($relativePath, 'prompt-context')) {
                continue;
            }

            $content = file_get_contents($realPath);

            // Verify content is actually text (UTF-8) before processing
            // This catches binary files that don't match the extension filters
            if (!mb_check_encoding($content, 'UTF-8')) {
                $this->warn("Skipping binary file: {$relativePath}");
                continue;
            }

            $this->line("Processing: <info>{$relativePath}</info>");

            $fileBlock = "FILE_PATH: {$relativePath}\n";
            $fileBlock .= "--- CONTENT START ---\n";
            $fileBlock .= $content . "\n";
            $fileBlock .= "--- CONTENT END ---\n";
            $fileBlock .= "==================================================\n\n";

            // If adding this file exceeds the limit, save the current chunk first
            if (strlen($currentChunkContent . $fileBlock) > $limit && !empty($currentChunkContent)) {
                $this->saveChunk($outputDir, $chunkIndex, $currentChunkContent, false);
                $currentChunkContent = "";
                $chunkIndex++;
            }

            $currentChunkContent .= $fileBlock;
        }

        // Final Chunk processing - append instructions and user prompt
        $finalInstructions = "\n### INSTRUCTIONS & GUIDELINES ###\n";
        $finalInstructions .= "1. Strictly follow the coding style found in the provided files.\n";
        $finalInstructions .= "2. Return FULL CODE for all modified components.\n";
        $finalInstructions .= "3. Do not omit any existing logic or provided variables.\n\n";
        $finalInstructions .= "### MY PROMPT ###\n";
        $finalInstructions .= $customPrompt . "\n";

        // Save the final piece
        $this->saveChunk($outputDir, $chunkIndex, $currentChunkContent . $finalInstructions, true);

        $this->info("\nSuccessfully collated context into " . $chunkIndex . " files in /prompt-context");

        return 0;
    }

    /**
     * Helper to save the file with appropriate wait/ready message.
     */
    private function saveChunk($dir, $index, $content, $isLast)
    {
        $header = "### CHUNK {$index} ###\n";
        $header .= "==================================================\n\n";

        $footer = "\n\n==================================================\n";
        if ($isLast) {
            $footer .= "### ALL DATA SENT. YOU MAY NOW RESPOND TO THE PROMPT ABOVE. ###";
        } else {
            $footer .= "### DO NOT RESPOND YET. I am sharing more information in subsequent chunks. Acknowledge briefly that you have received part {$index} and are waiting for more. ###";
        }

        File::put($dir . "/{$index}.txt", $header . $content . $footer);
    }
}
