<?php

namespace MBober35\Backups\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PushAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:push {period=daily} {--from-current} {--folder=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push app zip by period';

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
        $currentPath = "{$folder}/{$fileName}";
        $s3Folder = $this->option("folder");
        if (empty($s3Folder)) $s3Folder = config("backups.folder");
        $s3Folder .= "/";
        if ($this->option("from-current") && Storage::disk("backups")->exists($currentPath)) {
            try {
                Storage::disk("backups")->copy($currentPath, $fileName);
            }
            catch (\Exception $exception) {
                $this->error("File already exist");
            }
        }
        if (! Storage::disk("backups")->exists($fileName)) {
            $this->error("File not found");
            return;
        }


        try {
            Storage::disk("ya-backups")->put(
                $s3Folder . $fileName,
                Storage::disk("backups")->get($fileName)
            );
            $this->info("Backup send to cloud");
            if ($this->option("from-current") && Storage::disk("backups")->exists($currentPath)) {
                Storage::disk("backups")->delete($currentPath);
            }
            Storage::disk("backups")->delete($fileName);
        }
        catch (\Exception $exception) {
            $this->line($exception->getMessage());
        }
    }
}
