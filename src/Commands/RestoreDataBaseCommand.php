<?php

namespace MBober35\Backups\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RestoreDataBaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:db {table?} {--file=backup.sql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore app database';

    protected $username;
    protected $password;
    protected $database;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->username = config("database.connections.mysql.username");
        $this->password = config("database.connections.mysql.password");
        $this->database = config("database.connections.mysql.database");
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // File.
        $file = $this->option("file");
        if (! Storage::disk("backups")->exists($file)) {
            $this->error("File not found");
            Log::error("Data Base file not found");
            return;
        }
        // Command data.
        $password = $this->password;
        $db = $this->database;
        if ($table = $this->argument("table")) {
            $password = "";
            $db .= " $table";
        }
        // Make command.
        $process = Process::fromShellCommandline(sprintf(
            'mysql -u%s -p%s --default-character-set utf8 %s < %s',
            $this->username,
            $password,
            $db,
            backup_path($this->option("file"))
        ));

        try {
            // Run command.
            $process->mustRun();

            Storage::disk("backups")->delete($file);
            $this->info("The restore has been processed successfully");
        }
        catch (ProcessFailedException $exception) {
            $this->error("The backup process has been failed");
            $this->info($exception->getMessage());
            Log::error("The backup of data base process has been failed");
        }
    }
}
