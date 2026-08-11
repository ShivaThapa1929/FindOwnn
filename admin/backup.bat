@echo off
echo ============================================
echo Database Backup Script
echo ============================================
echo.

set TIMESTAMP=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set FILENAME=findownn_admin_%TIMESTAMP%.sql
set BACKUP_DIR=storage\backups

echo Creating backup directory...
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Backing up database: findownn_admin
echo Output file: %BACKUP_DIR%\%FILENAME%
echo.

C:\xampp\mysql\bin\mysqldump.exe -u root findownn_admin > "%BACKUP_DIR%\%FILENAME%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================
    echo SUCCESS! Backup completed.
    echo ============================================
    echo File: %BACKUP_DIR%\%FILENAME%
    for %%A in ("%BACKUP_DIR%\%FILENAME%") do echo Size: %%~zA bytes
) else (
    echo.
    echo ============================================
    echo ERROR! Backup failed.
    echo ============================================
)

echo.
pause
