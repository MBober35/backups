# Backups

Команды для архивировани приложения: база данных, storage/app/public

Архивы можно отправить в Yandex Object Storage

## Install

    YA_BACKUPS_CLOUD_ID = Id ключа
    YA_BACKUPS_CLOUD_SECRET = Secret ключа
    YA_BACKUPS_CLOUD_BUCKET = Бакет
    YA_BACKUPS_CLOUD_FOLDER = Папка в бакете

## Usage

`backup:app {period=daily} {--folder=} {--cloud}` - Создание архива и отправка в облако

`restore:app {period=daily} {--folder=} {--from-cloud}` - Скачивание архива и восстановление

`backup:list` - Список текущих бэкапов в облаке