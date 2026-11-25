@echo off
for /F %%a in ('echo prompt $E ^| cmd') do set "ESC=%%a"

set "YELLOW=%ESC%[93m"
set "GREEN=%ESC%[92m"
set "RED=%ESC%[91m"
set "RESET=%ESC%[0m"

title Stray Safe - AI Backend

cd /d "%~dp0"

echo =====================================
echo       STRAY SAFE AI BACKEND
echo =====================================
echo.

REM Check virtual environment
if not exist ".venv\Scripts\python.exe" (
    echo ERROR: Virtual environment not found.
    echo Please run SETUP.bat first.
    pause
    exit /b 1
)

REM Check backend files
if not exist "pet_recognition\app.py" (
    echo ERROR: app.py not found inside pet_recognition.
    pause
    exit /b 1
)

REM Check trained model
if not exist "pet_recognition\pet_breed_mobilenet_finetuned.keras" (
    echo ERROR: Breed model not found.
    pause
    exit /b 1
)

echo Starting FastAPI server...
echo Address: http://127.0.0.1:8000
echo Press CTRL+C to stop the server.
echo.

echo.
echo %YELLOW%============================================================================%RESET%
echo %GREEN% STARTING BACKEND DONT CLOSE THIS TERMINAL FOR THE WHOLE SESSION OF WEBSITE%RESET%
echo %YELLOW%============================================================================%RESET%
echo.

REM Run backend from its own directory
cd /d "%~dp0pet_recognition"

"%~dp0.venv\Scripts\python.exe" -m uvicorn app:app --host 127.0.0.1 --port 8000

echo.
echo Backend stopped.
pause
