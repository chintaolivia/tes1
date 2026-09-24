@echo off
cd /d "%~dp0"
title Aplikasi Antrian Online Salt Bread - litdig_kelompok11
color 0E

echo ========================================================
echo        APLIKASI ANTRIAN ONLINE SALT BREAD
echo           Database: litdig_kelompok11 (MySQL)
echo ========================================================
echo.

set PHP_EXE=php
where.exe php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    if exist "C:\Users\hafiz\laragon\bin\php\php-8.4.14-nts-Win32-vs17-x64\php.exe" (
        set PHP_EXE=C:\Users\hafiz\laragon\bin\php\php-8.4.14-nts-Win32-vs17-x64\php.exe
    ) else if exist "C:\xampp\php\php.exe" (
        set PHP_EXE=C:\xampp\php\php.exe
    ) else (
        echo [ERROR] PHP tidak ditemukan di sistem PATH, Laragon, maupun XAMPP!
        echo Pastikan Laragon atau XAMPP aktif di komputer Anda.
        pause
        exit /b 1
    )
)

echo [1/3] Menyiapkan dan memverifikasi database litdig_kelompok11...
"%PHP_EXE%" setup.php >nul 2>&1

echo [2/3] Memeriksa status server port 8080...
netstat -ano | findstr :8080 | findstr LISTENING >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [INFO] Server web sudah aktif di port 8080!
    echo [3/3] Membuka browser ke http://localhost:8080 ...
    start http://localhost:8080
    echo ========================================================
    echo Server sudah berjalan dan aplikasi dibuka di browser!
    echo Alamat: http://localhost:8080
    echo ========================================================
    echo.
    echo Tekan tombol apa saja untuk menutup jendela ini (server tetap berjalan).
    pause >nul
    exit /b 0
)

echo [3/3] Menjalankan server web lokal (Port 8080)...
start http://localhost:8080
echo ========================================================
echo Server berjalan di: http://localhost:8080
echo CATATAN: Jendela hitam ini JANGAN DITUTUP selama memakai aplikasi.
echo Tekan Ctrl + C jika ingin mematikan server.
echo ========================================================
echo.

"%PHP_EXE%" -S 0.0.0.0:8080

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Server terhenti tidak normal dengan kode error: %ERRORLEVEL%
    pause
)

