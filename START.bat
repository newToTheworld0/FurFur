
@echo off
title Stray Safe - AI Backend

cd /d "%~dp0"

echo =====================================
echo       STRAY SAFE AI BACKEND
echo =====================================
echo.

if not exist ".venv\Scripts\activate.bat" (
    echo ERROR: Virtual environment not found.
    echo Please run setup_backend.bat first.
    pause
    exit /b 1
)

call ".venv\Scripts\activate.bat"

echo Starting FastAPI server...
echo Address: http://127.0.0.1:8000
echo Press CTRL+C to stop the server.
echo.

python -m uvicorn app:app --host 127.0.0.1 --port 8000

pause
