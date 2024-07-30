<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessFile;
use Illuminate\Support\Facades\Log;

class ProcessFileCommand extends Command
{
    protected $signature = 'process:file {filePath}';
    protected $description = 'Process a CSV file and dispatch the job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = $this->argument('filePath');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }

        if (!is_readable($filePath)) {
            $this->error("File is not readable: $filePath");
            return;
        }

        try {
            dispatch(new ProcessFile($filePath));
            $this->info('File processing job dispatched successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch ProcessFile job: ' . $e->getMessage());
            $this->error('Failed to dispatch the job. Check logs for details.');
        }
    }
}
