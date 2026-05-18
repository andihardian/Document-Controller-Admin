<?php

use Illuminate\Support\Facades\Schedule;

// ─── Backup Database: setiap hari jam 02:00 ──────────────────────────────────
Schedule::command('backup:run --only-db')
    ->dailyAt('02:00')
    ->name('daily-db-backup')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Daily database backup FAILED!');
    });

// ─── Backup Full (DB + Files): setiap Minggu jam 01:00 ───────────────────────
Schedule::command('backup:run')
    ->weeklyOn(0, '01:00')
    ->name('weekly-full-backup')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Weekly full backup FAILED!');
    });

// ─── Cleanup backup lama: setiap hari jam 03:00 ──────────────────────────────
Schedule::command('backup:clean')
    ->dailyAt('03:00')
    ->name('backup-cleanup')
    ->withoutOverlapping();

// ─── Monitor kesehatan backup: setiap hari jam 08:00 ─────────────────────────
Schedule::command('backup:monitor')
    ->dailyAt('08:00')
    ->name('backup-monitor');