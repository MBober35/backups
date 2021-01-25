<?php

namespace MBober35\Backups;

use Aws\Sdk;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as BaseProvider;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use MBober35\Backups\Commands\BackupAppCommand;
use MBober35\Backups\Commands\BackupDataBaseCommand;
use MBober35\Backups\Commands\BackupStorageCommand;
use MBober35\Backups\Commands\PushAppCommand;

class ServiceProvider extends BaseProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/backups.php', "backups"
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Commands.
        $this->commands([
            BackupDataBaseCommand::class,
            BackupStorageCommand::class,
            BackupAppCommand::class,
            PushAppCommand::class,
        ]);
        // Добавить конфигурацию для файловой системы.
        app()->config['filesystems.disks.backups'] = [
            'driver' => 'local',
            'root' => backup_path(),
        ];
        app()->config['filesystems.disks.ya-backups'] = [
            'driver' => "yaS3Backups",
            "key" => config("backups.keyId"),
            'secret' => config("backups.keySecret"),
            'region' => config("backups.region"),
            'bucket' => config("backups.bucket"),
        ];

        // Yandex cloud storage.
        Storage::extend("yaS3Backups", function ($app, $config) {
            $configS3 = [
                "endpoint" => "https://storage.yandexcloud.net",
                "region" => $config['region'],
                "version" => "latest",
                "credentials" => [
                    "key" => $config["key"],
                    "secret" => $config["secret"],
                ],
            ];
            if (config("app.debug")) {
                $configS3['http'] = [
                    'verify' => false
                ];
            }
            $sdk = new Sdk($configS3);
            $s3 = $sdk->createS3();

            return new Filesystem(new AwsS3Adapter($s3, $config['bucket']));
        });
    }
}
