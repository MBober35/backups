<?php

namespace MBober35\Backups\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:list {--folder=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $folder = $this->option("folder");
        $folder = empty($folder) ? config("backups.folder") : $folder;

        try {
            foreach (Storage::disk("ya-backups")->files($folder) as $fileName) {
                $size = Storage::disk("ya-backups")->size($fileName);
                $format = $this->formatBytes($size);

                $ts = Storage::disk("ya-backups")->lastModified($fileName);
                $carbon = Carbon::createFromTimestamp($ts);
                $carbon->timezone = "Europe/Moscow";

                $this->info("{$fileName} : {$format} : {$carbon->format('d.m.Y H:i:s')}");
            }
        } catch (\Exception $exception) {
            $this->error("Some error");
        }
    }

    /**
     * Формат размера.
     *
     * @param $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
