<?php

return [

    'backup' => [

        'name' => env('BACKUP_NAME', env('APP_NAME', 'laravel-backup')),

        'source' => [

            'files' => [
                'include' => [
                    storage_path('app/public/documents'),
                ],

                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('logs'),
                    storage_path('app/backup-temp'),
                    base_path('.git'),
                ],

                'follow_links'                    => false,
                'ignore_unreadable_directories'   => false,
                'relative_path'                   => base_path(),
            ],

            'databases' => [
                'mysql',
            ],
        ],

        'database_dump_compressor' => null,

        'database_dump_file_extension' => '',

        'destination' => [

            'filename_prefix' => '',

            // Backup ke local DAN Google Drive sekaligus
            'disks' => [
                'local',
            ],
        ],

        'password'   => env('BACKUP_ARCHIVE_PASSWORD', null),
        'encryption' => 'default',
        'tries'      => 1,
        'retry_delay' => 0,
    ],

    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class          => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class        => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class     => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class   => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class    => ['mail'],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to'   => env('BACKUP_NOTIFICATION_EMAIL', 'admin@example.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'backup@example.com'),
                'name'    => env('MAIL_FROM_NAME', 'Backup System'),
            ],
        ],

        'slack'   => ['webhook_url' => '', 'channel' => null, 'username' => null, 'icon' => null],
        'discord' => ['webhook_url' => '', 'username' => '', 'avatar_url' => ''],
    ],

    'monitor_backups' => [
        [
            'name'          => env('BACKUP_NAME', env('APP_NAME', 'laravel-backup')),
            'disks'         => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class          => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days'                          => 7,
            'keep_daily_backups_for_days'                        => 16,
            'keep_weekly_backups_for_weeks'                      => 8,
            'keep_monthly_backups_for_months'                    => 4,
            'keep_yearly_backups_for_years'                      => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 10240,
        ],
    ],

];

