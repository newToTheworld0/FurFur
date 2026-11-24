
@echo off
title Stray Safe - Backend Setup

cd /d "%~dp0"

echo =====================================
echo      STRAY SAFE BACKEND SETUP
echo =====================================
echo.

echo [1/2] Creating Python virtual environment...

if not exist ".venv\Scripts\python.exe" (
    py -3.13 -m venv .venv
    if errorlevel 1 (
        echo ERROR: Failed to create virtual environment.
        pause
        exit /b 1
    )
) else (
    echo Virtual environment already exists.
)

echo.
echo [2/2] Installing dependencies...

".venv\Scripts\python.exe" -m pip install --upgrade pip
if errorlevel 1 (
    echo ERROR: Failed to upgrade pip.
    pause
    exit /b 1
)

".venv\Scripts\python.exe" -m pip install -r requirements.txt
if errorlevel 1 (
    echo ERROR: Failed to install dependencies.
    pause
    exit /b 1
)

echo.
echo =====================================
echo       SETUP COMPLETED!
echo =====================================
pause
