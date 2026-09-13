@echo off
if "%1"=="" (
    python ftp_sync.py test
) else (
    python ftp_sync.py %1
)
