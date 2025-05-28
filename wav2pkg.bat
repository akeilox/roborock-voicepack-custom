@echo off
cd %~dp0
start "WAV2PKG" environment\php\php.exe -c environment\php\php.ini environment\wav2pkg.php