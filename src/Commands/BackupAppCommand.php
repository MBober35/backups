<?php

namespace MBober35\Backups\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZanySoft\Zip\Zip;

class BackupAppCommand extends Command
{
    const FOLDER = "current";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:app {period=daily} {--folder=} {--cloud}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup app files by period';

    protected $zip;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->zip = null;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Backup storage.
        $this->callSilent("backup:storage");
        if (! Storage::disk("backups")->exists(BackupStorageCommand::FILE_NAME)) {
            $this->error("Backup storage failed");
            return;
        }
        // Backup database.
        $this->callSilent("backup:db");
        if (! Storage::disk("backups")->exists(BackupDataBaseCommand::FILE_NAME)) {
            $this->error("Backup database failed");
            return;
        }
        // Make archive.
        $period = $this->argument("period");
        $filename = "{$period}.zip";

        if (Storage::disk("backups")->exists($filename)) {
            Storage::disk("backups")->delete($filename);
        }

        try {
            $this->zip = Zip::create(backup_path($filename));
        }
        catch (\Exception $exception) {
            $this->zip = null;
        }

        if (! $this->zip) {
            $this->error("Fail init archive");
            return;
        }

        try {
            $this->zip->add([
                backup_path(BackupDataBaseCommand::FILE_NAME),
                backup_path(BackupStorageCommand::FILE_NAME),
            ]);
            $this->zip->close();

            Storage::disk("backups")->delete([
                BackupDataBaseCommand::FILE_NAME,
                BackupStorageCommand::FILE_NAME,
            ]);

            $folder = self::FOLDER;

            if (Storage::disk("backups")->exists("{$folder}/{$filename}")) {
                Storage::disk("backups")->delete("{$folder}/{$filename}");
            }
            Storage::disk("backups")->move($filename, "{$folder}/{$filename}");

            $this->info("Backup {$period} create successfully");
        }
        catch (\Exception $exception) {
            $this->error("Error while generated archive");
            $this->line($exception->getMessage());
            return;
        }

        if ($this->option("cloud")) {
            $s3Folder = $this->option("folder");
             if (empty($s3Folder)) $s3Folder = config("backups.folder");
             $this->call("backup:push", [
                 "period" => $period,
                 "--from-current" => true,
                 "--folder" => $s3Folder,
             ]);
        }
    }
}
