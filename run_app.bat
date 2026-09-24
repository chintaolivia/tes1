@echo off
setlocal enabledelayedexpansion
title Little Salt Bread Blok M - Local Server Runner

echo ================================================================
echo    LITTLE SALT BREAD BLOK M - POS ^& ORDERING SYSTEM (PWA)
echo ================================================================
echo.

set PHP_EXE=

:: 1. Check system PATH
where php >nul 2>nul
if %errorlevel% equ 0 (
    set PHP_EXE=php
    goto :FOUND
)

:: 2. Check Laragon default paths
for /d %%D in (C:\laragon\bin\php\php-*) do (
    if exist "%%D\php.exe" (
        set "PHP_EXE=%%D\php.exe"
        goto :FOUND
    )
)
for /d %%D in (D:\laragon\bin\php\php-*) do (
    if exist "%%D\php.exe" (
        set "PHP_EXE=%%D\php.exe"
        goto :FOUND
    )
)

:: 3. Check XAMPP default paths
if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE=C:\xampp\php\php.exe"
    goto :FOUND
)
if exist "D:\xampp\php\php.exe" (
    set "PHP_EXE=D:\xampp\php\php.exe"
    goto :FOUND
)

echo [PERINGATAN] PHP tidak ditemukan di PATH, Laragon, atau XAMPP standard.
echo Jika Anda menggunakan Laragon / XAMPP Virtual Host:
echo   - Cukup akses via browser ke folder ini melalui host Laragon Anda
echo.
pause
exit /b 1

:FOUND
echo [OK] Menggunakan PHP: %PHP_EXE%
echo [INFO] Menjalankan Server Lokal di: http://localhost:8080
echo [INFO] Tekan Ctrl+C untuk menghentikan server.
echo.

:: Open browser after 1 second delay
start "" http://localhost:8080

:: Run PHP built-in server
"%PHP_EXE%" -S 0.0.0.0:8080 -t "%~dp0"
pause

