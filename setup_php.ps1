# PowerShell script to download and setup portable PHP + MongoDB + Composer locally

$ErrorActionPreference = "Stop"

$rootDir = "d:\WEBSITE\MASAR-AGENCY-main"
$phpDir = "$rootDir\php-bin"
$phpZip = "$rootDir\php.zip"
$mongoZip = "$rootDir\mongo.zip"

# Create php-bin directory
if (-not (Test-Path $phpDir)) {
    New-Item -ItemType Directory -Path $phpDir | Out-Null
    Write-Host "✅ Created directory: $phpDir"
}

# 1. Download PHP 8.2.19
if (-not (Test-Path "$phpDir\php.exe")) {
    Write-Host "⏳ Downloading PHP 8.2.19 (x64 Thread Safe)..."
    $phpUrl = "https://windows.php.net/downloads/releases/php-8.2.19-Win32-vs16-x64.zip"
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip
    
    Write-Host "⏳ Extracting PHP..."
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force
    Remove-Item $phpZip
    Write-Host "✅ PHP extracted successfully."
} else {
    Write-Host "✅ PHP is already installed."
}

# 2. Download and install MongoDB PHP extension (1.17.2 for PHP 8.2 TS x64)
if (-not (Test-Path "$phpDir\ext\php_mongodb.dll")) {
    Write-Host "⏳ Downloading MongoDB extension..."
    $mongoUrl = "https://windows.php.net/downloads/pecl/releases/mongodb/1.17.2/php_mongodb-1.17.2-8.2-ts-vs16-x64.zip"
    Invoke-WebRequest -Uri $mongoUrl -OutFile $mongoZip
    
    Write-Host "⏳ Extracting MongoDB extension dll..."
    # Extract to a temp folder first
    $tempMongoDir = "$rootDir\mongo-temp"
    if (Test-Path $tempMongoDir) { Remove-Item $tempMongoDir -Recurse -Force }
    Expand-Archive -Path $mongoZip -DestinationPath $tempMongoDir
    
    # Copy dll to ext/
    Copy-Item "$tempMongoDir\php_mongodb.dll" -Destination "$phpDir\ext\" -Force
    
    # Clean up temp
    Remove-Item $tempMongoDir -Recurse -Force
    Remove-Item $mongoZip
    Write-Host "✅ MongoDB extension installed."
} else {
    Write-Host "✅ MongoDB extension is already installed."
}

# 3. Create php.ini configuration file
Write-Host "⏳ Configuring php.ini..."
$iniPath = "$phpDir\php.ini"
$iniContent = @"
[PHP]
extension_dir = "ext"
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=gd
extension=zip
extension=mongodb

max_execution_time = 300
memory_limit = 512M
post_max_size = 100M
upload_max_filesize = 100M
date.timezone = UTC
"@
Set-Content -Path $iniPath -Value $iniContent -Encoding UTF8
Write-Host "✅ php.ini configured."

# 4. Download Composer
if (-not (Test-Path "$phpDir\composer.phar")) {
    Write-Host "⏳ Downloading Composer..."
    $composerUrl = "https://getcomposer.org/composer.phar"
    Invoke-WebRequest -Uri $composerUrl -OutFile "$phpDir\composer.phar"
    Write-Host "✅ Composer downloaded."
} else {
    Write-Host "✅ Composer is already present."
}

# 5. Run Composer Install for the backend
Write-Host "⏳ Running composer install in backend/..."
$phpExe = "$phpDir\php.exe"
$composerPhar = "$phpDir\composer.phar"

# Run composer command using local PHP executable
Start-Process -FilePath $phpExe -ArgumentList "$composerPhar install --working-dir=$rootDir\backend" -NoNewWindow -Wait
Write-Host "✅ Composer dependencies installed successfully!"

# 6. Run local server
Write-Host "🚀 Launching PHP local server on http://localhost:3000..."
Start-Process -FilePath $phpExe -ArgumentList "-S localhost:3000 $rootDir\backend\public\index.php" -NoNewWindow
Write-Host "✅ Server is running. Open http://localhost:3000 in your browser."
