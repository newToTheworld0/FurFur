@echo off
for /F %%a in ('echo prompt $E ^| cmd') do set "ESC=%%a"

set "YELLOW=%ESC%[93m"
set "GREEN=%ESC%[92m"
set "RED=%ESC%[91m"
set "RESET=%ESC%[0m"

title Stray Safe - Backend and Database Setup

cd /d "%~dp0"

echo =====================================
echo       STRAY SAFE PROJECT SETUP
echo =====================================
echo.

echo [1/4] Checking Python 3.13...

if exist ".venv\Scripts\python.exe" (
    echo Existing virtual environment detected.
    goto CHECK_DATABASE
)

py -3.13 --version >nul 2>&1

if not errorlevel 1 goto CREATE_VENV

echo Python 3.13 not found. Installing...

where winget >nul 2>&1

if errorlevel 1 (
    echo ERROR: winget is unavailable.
    echo Install Python 3.13 manually:
    echo https://www.python.org/downloads/
    pause
    exit /b 1
)

winget install --id Python.Python.3.13 --exact --scope user --silent --accept-package-agreements --accept-source-agreements

if errorlevel 1 (
    echo ERROR: Python installation failed.
    pause
    exit /b 1
)

py -3.13 --version >nul 2>&1

if errorlevel 1 (
    echo %GREEN%========================================%RESET%
    echo %YELLOW%???? ---- RERUN the SETUP.bat after new Python just Installed and it will work accordingly ---- ???? %RESET%
    echo %GREEN%========================================%RESET%
    pause
    exit /b 1
)

:CREATE_VENV

echo.
echo %ESC%[92mCreating virtual environment...%ESC%[0m

py -3.13 -m venv .venv

if errorlevel 1 (
    echo ERROR: Failed to create virtual environment.
    pause
    exit /b 1
)

echo.
echo Installing Python dependencies...

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

:CHECK_DATABASE

echo.
echo [2/4] Checking XAMPP database server...

set "MYSQL=C:\xampp\mysql\bin\mysql.exe"
set "SQL_FILE=%~dp0database\stray_safe_db.sql"

if not exist "%MYSQL%" (
    echo ERROR: XAMPP MariaDB client not found.
    echo Expected location: %MYSQL%
    echo Check your XAMPP installation.
    pause
    exit /b 1
)

if not exist "%SQL_FILE%" (
    echo ERROR: SQL template not found:
    echo %SQL_FILE%
    pause
    exit /b 1
)

"%MYSQL%" -u root -e "SELECT 1;" >nul 2>&1

if errorlevel 1 (
    echo ERROR: Cannot connect to MariaDB.
    echo Start MySQL from the XAMPP Control Panel first.
    pause
    exit /b 1
)

echo Database server is running.

echo.
echo [3/4] Checking Stray Safe database...

"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='stray_safe_db';" > "%TEMP%\stray_safe_db_count.txt"

if errorlevel 1 (
    echo ERROR: Could not check database.
    pause
    exit /b 1
)

set /p TABLE_COUNT=<"%TEMP%\stray_safe_db_count.txt"
del "%TEMP%\stray_safe_db_count.txt"

if not "%TABLE_COUNT%"=="0" (
    echo Database already contains tables.
    echo Skipping import to protect existing data.
    goto SETUP_COMPLETE
)

echo Creating and importing database...

"%MYSQL%" -u root < "%SQL_FILE%"

if errorlevel 1 (
    echo ERROR: Database import failed.
    pause
    exit /b 1
)

echo Database imported successfully.

:SETUP_COMPLETE

echo.
echo [4/4] Setup verification...

echo.
echo =====================================
echo        SETUP COMPLETED!
echo =====================================
echo.
echo Python environment: .venv
echo Database: stray_safe_db
echo.
echo You can now run START.bat.
echo.
pause