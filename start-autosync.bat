@echo off
title Zamzy cPanel Auto-Sync Watcher
echo ======================================================
echo    Starting Automatic Zamzy cPanel FTP Sync...
echo ======================================================
python ftp_sync.py watch
pause
