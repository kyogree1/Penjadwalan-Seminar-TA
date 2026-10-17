@echo off
title SIPTA IF - Penjadwalan Seminar TA Dev Server
cd /d "%~dp0"

set "PATH=C:\laragon\bin\php\php-8.4.26-Win32-vs17-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin;C:\Program Files\nodejs;%PATH%"

echo ========================================================
echo    SIPTA Informatika ITK - Penjadwalan Seminar TA
echo ========================================================
echo.
echo Server dan Vite sedang berjalan...
echo Silakan buka browser kamu di:
echo    http://localhost:8000/login
echo.
echo Akun Koorprodi / Kaprodi:
echo   Username: koorprodi  (atau 198003152005011002)
echo   Password: password
echo.
echo (Tekan Ctrl+C untuk menghentikan server jika sudah selesai)
echo ========================================================
echo.

call composer.bat dev
pause
