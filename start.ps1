# Starts Lab 11 (Vault Terminal) at http://localhost:8113
# Loads pdo_sqlite explicitly, because the winget PHP build has no php.ini.
$php = (Get-Command php -ErrorAction Stop).Source
$ext = Join-Path (Split-Path $php) 'ext'
Write-Host "Lab 11 (Vault Terminal) running at http://localhost:8113  (Ctrl+C to stop)"
& $php -d "extension_dir=$ext" -d extension=pdo_sqlite -S localhost:8113 -t $PSScriptRoot
