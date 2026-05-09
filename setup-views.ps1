# setup-views.ps1
# Jalankan dari dalam folder document-control-system

$base = "resources\views"

# Buat semua direktori
$dirs = @(
    "$base\layouts",
    "$base\components",
    "$base\dashboard",
    "$base\documents",
    "$base\approvals",
    "$base\users",
    "$base\departments",
    "$base\categories",
    "$base\audit-logs"
)

foreach ($dir in $dirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "Created: $dir" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Direktori views berhasil dibuat!" -ForegroundColor Cyan
Write-Host ""
Write-Host "Sekarang copy file .blade.php yang sudah didownload ke folder berikut:" -ForegroundColor Yellow
Write-Host ""
Write-Host "  layouts\app.blade.php               -> $base\layouts\"
Write-Host "  dashboard\admin.blade.php           -> $base\dashboard\"
Write-Host "  dashboard\department_head.blade.php -> $base\dashboard\"
Write-Host "  dashboard\employee.blade.php        -> $base\dashboard\"
Write-Host "  documents\index.blade.php           -> $base\documents\"
Write-Host "  documents\create.blade.php          -> $base\documents\"
Write-Host "  documents\show.blade.php            -> $base\documents\"
Write-Host "  documents\edit.blade.php            -> $base\documents\"
Write-Host "  approvals\index.blade.php           -> $base\approvals\"
Write-Host "  approvals\show.blade.php            -> $base\approvals\"
Write-Host "  users\index.blade.php               -> $base\users\"
Write-Host "  users\create.blade.php              -> $base\users\"
Write-Host "  users\edit.blade.php                -> $base\users\"
Write-Host "  departments\index.blade.php         -> $base\departments\"
Write-Host "  departments\create.blade.php        -> $base\departments\"
Write-Host "  departments\edit.blade.php          -> $base\departments\"
Write-Host "  categories\index.blade.php          -> $base\categories\"
Write-Host "  categories\create.blade.php         -> $base\categories\"
Write-Host "  categories\edit.blade.php           -> $base\categories\"
Write-Host "  audit-logs\index.blade.php          -> $base\audit-logs\"
Write-Host ""

# Buat symlink storage jika belum ada
if (-not (Test-Path "public\storage")) {
    Write-Host "Membuat storage symlink..." -ForegroundColor Yellow
    php artisan storage:link
}

Write-Host ""
Write-Host "Selesai! Jalankan: php artisan serve + npm run dev" -ForegroundColor Cyan
