@echo off
chcp 65001 >nul
title Masar - Sync to GitHub
set PATH=C:\Users\mzeya\AppData\Local\Programs\Git\cmd;%PATH%

echo ========================================================
echo         Masar - جاري رفع التحديثات إلى GitHub...
echo ========================================================

git add .
set commit_msg=%*
if "%commit_msg%"=="" (
    set commit_msg=Auto update on %date% at %time%
)

git commit -m "%commit_msg%"
git push origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================================
    echo   [✔] تم الرفع بنجاح إلى GitHub!
    echo ========================================================
) else (
    echo.
    echo ========================================================
    echo   [!] تنبيه: حدث خطأ أثناء الرفع. يرجى التأكد من الرابط والصلاحيات.
    echo ========================================================
)

timeout /t 5
