@echo off
REM Pre-Deployment Checklist Script for Windows
REM This script ensures all critical checks pass before deployment

echo ==========================================
echo Pre-Deployment Checklist
echo ==========================================
echo.

set ERRORS=0
set WARNINGS=0

REM Check 1: Verify public/hot doesn't exist
echo 1. Checking for public/hot file...
if exist "public\hot" (
    echo    [FAIL] public/hot exists - this will cause Vite server dependency issues
    echo    Run: del public\hot
    set /a ERRORS+=1
) else (
    echo    [PASS]
)

REM Check 2: Verify build manifest exists
echo 2. Checking for build manifest...
if exist "public\build\manifest.json" (
    echo    [PASS]
) else (
    echo    [FAIL] public/build/manifest.json not found
    echo    Run: npm run build
    set /a ERRORS+=1
)

REM Check 3: Check for hardcoded Vite URLs
echo 3. Checking for hardcoded Vite URLs...
findstr /s /i /c:"http://localhost:5173" resources\views\*.blade.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo    [FAIL] Found hardcoded Vite dev server URLs
    echo    Files:
    findstr /s /i /c:"http://localhost:5173" resources\views\*.blade.php | findstr /v "^$"
    set /a ERRORS+=1
) else (
    echo    [PASS]
)

REM Check 4: Verify @vite directives are used
echo 4. Checking for @vite directives...
findstr /s /i /c:"@vite" resources\views\*.blade.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo    [PASS]
) else (
    echo    [WARNING] No @vite directives found - verify assets are loaded correctly
    set /a WARNINGS+=1
)

REM Check 5: Verify cache clearing in module controllers
echo 5. Checking cache clearing in ModuleController...
findstr /c:"Cache::forget" app\Http\Controllers\Teacher\ModuleController.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo    [PASS]
) else (
    echo    [WARNING] Cache clearing may be missing in ModuleController
    set /a WARNINGS+=1
)

REM Check 6: Verify cache clearing in API controller
echo 6. Checking cache clearing in CourseHierarchyController...
findstr /c:"Cache::forget" app\Http\Controllers\Api\CourseHierarchyController.php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo    [PASS]
) else (
    echo    [WARNING] Cache clearing may be missing in CourseHierarchyController
    set /a WARNINGS+=1
)

REM Check 7: Verify student module view exists
echo 7. Checking for student module view...
if exist "resources\views\student\courses\module.blade.php" (
    echo    [PASS]
) else (
    echo    [FAIL] resources/views/student/courses/module.blade.php not found
    set /a ERRORS+=1
)

REM Check 8: Verify route naming in student module view
echo 8. Checking route naming in student module view...
if exist "resources\views\student\courses\module.blade.php" (
    findstr /c:"student.courses.modules.subpages.show" resources\views\student\courses\module.blade.php >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo    [PASS]
    ) else (
        echo    [FAIL] Incorrect route name in student module view
        set /a ERRORS+=1
    )
) else (
    echo    [SKIP]
)

REM Check 9: Verify npm packages are installed
echo 9. Checking npm packages...
if exist "node_modules" (
    echo    [PASS]
) else (
    echo    [FAIL] node_modules not found
    echo    Run: npm install
    set /a ERRORS+=1
)

REM Check 10: Verify composer packages are installed
echo 10. Checking composer packages...
if exist "vendor" (
    echo    [PASS]
) else (
    echo    [FAIL] vendor directory not found
    echo    Run: composer install
    set /a ERRORS+=1
)

echo.
echo ==========================================
echo Summary
echo ==========================================
echo Errors: %ERRORS%
echo Warnings: %WARNINGS%
echo.

if %ERRORS% GTR 0 (
    echo [FAIL] DEPLOYMENT BLOCKED
    echo Please fix the errors above before deploying.
    exit /b 1
) else if %WARNINGS% GTR 0 (
    echo [WARNING] WARNINGS FOUND
    echo Review warnings above. Deployment can proceed but issues may exist.
    exit /b 0
) else (
    echo [PASS] ALL CHECKS PASSED
    echo Ready for deployment!
    exit /b 0
)
