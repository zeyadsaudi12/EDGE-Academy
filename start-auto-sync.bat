@echo off
chcp 65001 >nul
title Masar - GitHub Auto Sync
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0auto-sync.ps1"
pause
