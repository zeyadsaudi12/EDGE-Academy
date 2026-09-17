@echo off
setlocal enabledelayedexpansion
title Masar - Sync to GitHub
set PATH=C:\Users\mzeya\AppData\Local\Programs\Git\cmd;%PATH%
cd /d "%~dp0"

echo ========================================================
echo         Masar - Syncing project with GitHub...
echo ========================================================

git add .

set "MSG=%~1"
if "!MSG!"=="" set "MSG=Update %DATE% %TIME%"

git commit -m "!MSG!"
if %ERRORLEVEL% EQU 0 (
    git push origin main
    echo.
    echo ========================================================
    echo   [SUCCESS] Pushed changes to GitHub successfully!
    echo ========================================================
) else (
    echo.
    echo [INFO] Everything is already up to date.
)

echo.
echo Closing in 3 seconds...
ping 127.0.0.1 -n 4 >nul
