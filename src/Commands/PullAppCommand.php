<?php

namespace MBober35\Backups\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PullAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:pull {period=daily} {--folder=} {--to-current}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull app zip by period';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $period = $this->argument("period");
        $fileName = "{$period}.zip";
        $folder = BackupAppCommand::FOLDER;
        $currentFile = "{$folder}/{$fileName}";
        $s3Folder = $this->option("folder");
        if (empty($s3Folder)) $s3Folder = config("backups.folder");
        $s3Folder .= "/";
        $s3File = $s3Folder . $fileName;
        $filePath = $this->option("to-current") ? $currentFile : $fileName;
        if (Storage::disk("ya-backups")->exists($s3File)) {
            Storage::disk("backups")->put(
                $filePath,
                Storage::disk("ya-backups")->get($s3File)
            );
            $this->info("{$fileName} downloaded");
        }
        else {
            $this->error("File not found");
        }
    }
}
