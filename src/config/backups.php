<?php

return [
    "keyId" => env("YA_BACKUPS_CLOUD_ID", ""),
    "keySecret" => env("YA_BACKUPS_CLOUD_SECRET", ""),
    "bucket" => env("YA_BACKUPS_CLOUD_BUCKET", ""),
    "region" => env("YA_BACKUPS_CLOUD_REGION", "ru-central1"),
    "folder" => env("YA_BACKUPS_CLOUD_FOLDER", ""),
];
