# PowerShell script to configure XAMPP PHP with MongoDB and Composer (Updated)

$ErrorActionPreference = "Stop"

$phpPath = "C:\xampp\php"
$phpExe = "$phpPath\php.exe"
$phpIni = "$phpPath\php.ini"
$extDir = "$phpPath\ext"
$rootDir = "d:\WEBSITE\MASAR-AGENCY-main"
$mongoZip = "$rootDir\mongo.zip"

Write-Host "⚙️ Configuring XAMPP PHP..."

# 1. Download and install MongoDB PHP extension (1.17.2 for PHP 8.2 TS x64)
if (-not (Test-Path "$extDir\php_mongodb.dll")) {
    Write-Host "⏳ Downloading MongoDB extension for PHP 8.2 (Thread Safe, x64)..."
    $mongoUrl = "https://windows.php.net/downloads/pecl/releases/mongodb/1.17.2/php_mongodb-1.17.2-8.2-ts-vs16-x64.zip"
    Invoke-WebRequest -Uri $mongoUrl -OutFile $mongoZip
    
    Write-Host "⏳ Extracting php_mongodb.dll..."
    $tempMongoDir = "$rootDir\mongo-temp"
    if (Test-Path $tempMongoDir) { Remove-Item $tempMongoDir -Recurse -Force }
    Expand-Archive -Path $mongoZip -DestinationPath $tempMongoDir
    
    # Copy DLL to XAMPP's ext directory
    Copy-Item "$tempMongoDir\php_mongodb.dll" -Destination "$extDir\" -Force
    
    # Clean up temp files
    Remove-Item $tempMongoDir -Recurse -Force
    Remove-Item $mongoZip
    Write-Host "✅ php_mongodb.dll copied to $extDir"
} else {
    Write-Host "✅ php_mongodb.dll already exists in XAMPP."
}

# 2. Add extension=mongodb to php.ini and uncomment core extensions (gd, zip, openssl, curl, mbstring, fileinfo)
if (Test-Path $phpIni) {
    Write-Host "⏳ Updating php.ini configuration..."
    $iniContent = Get-Content -Path $phpIni -Raw
    
    # Uncomment core extensions (only if commented out, to avoid double loading warnings)
    $iniContent = $iniContent -replace ';extension=gd', 'extension=gd'
    $iniContent = $iniContent -replace ';extension=zip', 'extension=zip'
    $iniContent = $iniContent -replace ';extension=curl', 'extension=curl'
    $iniContent = $iniContent -replace ';extension=fileinfo', 'extension=fileinfo'
    $iniContent = $iniContent -replace ';extension=mbstring', 'extension=mbstring'
    
    # Add mongodb extension if not present
    if ($iniContent -notmatch "extension\s*=\s*mongodb") {
        $iniContent = $iniContent + "`r`nextension=mongodb"
    }
    
    Set-Content -Path $phpIni -Value $iniContent -Force
    Write-Host "✅ php.ini configuration updated."
} else {
    Write-Host "⚠️ php.ini not found at $phpIni!"
}

# 3. Download Composer locally to XAMPP PHP folder if not present
$composerPhar = "$phpPath\composer.phar"
if (-not (Test-Path $composerPhar)) {
    Write-Host "⏳ Downloading Composer..."
    $composerUrl = "https://getcomposer.org/composer.phar"
    Invoke-WebRequest -Uri $composerUrl -OutFile $composerPhar
    Write-Host "✅ Composer downloaded to XAMPP PHP directory."
} else {
    Write-Host "✅ Composer is already present in XAMPP."
}

# 4. Disable Composer security advisories blocking for this project
Write-Host "⏳ Disabling Composer security advisory blocks for backend..."
Start-Process -FilePath $phpExe -ArgumentList "$composerPhar config policy.advisories.block false --working-dir=$rootDir\backend" -NoNewWindow -Wait

# 5. Install Composer dependencies for the backend
Write-Host "⏳ Running composer install in backend/..."
$process = Start-Process -FilePath $phpExe -ArgumentList "$composerPhar install --working-dir=$rootDir\backend --no-security-blocking" -NoNewWindow -Wait -PassThru
if ($process.ExitCode -ne 0) {
    Write-Warning "⚠️ Composer installation returned exit code $($process.ExitCode). Trying with --ignore-platform-reqs..."
    Start-Process -FilePath $phpExe -ArgumentList "$composerPhar install --working-dir=$rootDir\backend --ignore-platform-reqs --no-security-blocking" -NoNewWindow -Wait
} else {
    Write-Host "✅ Composer dependencies installed successfully!"
}

# 6. Kill any process currently running on port 3000
Write-Host "⏳ Stopping existing processes on port 3000..."
try {
    $conn = Get-NetTCPConnection -LocalPort 3000 -ErrorAction SilentlyContinue
    if ($conn) {
        $pidToKill = $conn.OwningProcess | Select-Object -Unique
        foreach ($p in $pidToKill) {
            Stop-Process -Id $p -Force -ErrorAction SilentlyContinue
        }
        Write-Host "✅ Stopped processes on port 3000."
    }
} catch {
    Write-Host "Note: No active process found on port 3000."
}

# 7. Run the web server
Write-Host "🚀 Starting local server on http://localhost:3000..."
Start-Process -FilePath $phpExe -ArgumentList "-S localhost:3000 $rootDir\backend\public\index.php" -NoNewWindow
Write-Host "🚀 Server is running! Open http://localhost:3000 in your browser."
