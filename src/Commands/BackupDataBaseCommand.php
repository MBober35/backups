<?php

namespace MBober35\Backups\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDataBaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "backup:db {table?} {--file=backup.sql}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup app database';

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
        // Data.
        $password = $this->password;
        $db = $this->database;
        if ($table = $this->argument("table")) {
            $password = "";
            $db .= " $table";
        }
        // File.
        $file = $this->option("file");
        if (Storage::disk("backups")->exists($file)) {
            Storage::disk("backups")->delete($file);
        }
    }
}
