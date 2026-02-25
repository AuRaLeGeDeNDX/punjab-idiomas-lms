#!/bin/bash

# Pre-Deployment Checklist Script
# This script ensures all critical checks pass before deployment

echo "=========================================="
echo "Pre-Deployment Checklist"
echo "=========================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# Check 1: Verify public/hot doesn't exist
echo -n "1. Checking for public/hot file... "
if [ -f "public/hot" ]; then
    echo -e "${RED}FAIL${NC}"
    echo "   ❌ public/hot exists - this will cause Vite server dependency issues"
    echo "   Run: rm public/hot"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}PASS${NC}"
fi

# Check 2: Verify build manifest exists
echo -n "2. Checking for build manifest... "
if [ -f "public/build/manifest.json" ]; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC}"
    echo "   ❌ public/build/manifest.json not found"
    echo "   Run: npm run build"
    ERRORS=$((ERRORS + 1))
fi

# Check 3: Check for hardcoded Vite URLs
echo -n "3. Checking for hardcoded Vite URLs... "
HARDCODED=$(grep -r "http://localhost:5173" resources/views/ 2>/dev/null | wc -l)
if [ "$HARDCODED" -gt 0 ]; then
    echo -e "${RED}FAIL${NC}"
    echo "   ❌ Found $HARDCODED hardcoded Vite dev server URLs"
    echo "   Files:"
    grep -r "http://localhost:5173" resources/views/ 2>/dev/null | cut -d: -f1 | sort -u | sed 's/^/      /'
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}PASS${NC}"
fi

# Check 4: Verify @vite directives are used
echo -n "4. Checking for @vite directives... "
VITE_DIRECTIVES=$(grep -r "@vite" resources/views/ 2>/dev/null | wc -l)
if [ "$VITE_DIRECTIVES" -gt 0 ]; then
    echo -e "${GREEN}PASS${NC} (Found $VITE_DIRECTIVES usages)"
else
    echo -e "${YELLOW}WARNING${NC}"
    echo "   ⚠️  No @vite directives found - verify assets are loaded correctly"
    WARNINGS=$((WARNINGS + 1))
fi

# Check 5: Verify cache clearing in module controllers
echo -n "5. Checking cache clearing in ModuleController... "
CACHE_CLEARS=$(grep -c "Cache::forget" app/Http/Controllers/Teacher/ModuleController.php 2>/dev/null)
if [ "$CACHE_CLEARS" -ge 4 ]; then
    echo -e "${GREEN}PASS${NC} (Found $CACHE_CLEARS cache clears)"
else
    echo -e "${YELLOW}WARNING${NC}"
    echo "   ⚠️  Expected at least 4 cache clears in ModuleController (found $CACHE_CLEARS)"
    WARNINGS=$((WARNINGS + 1))
fi

# Check 6: Verify cache clearing in API controller
echo -n "6. Checking cache clearing in CourseHierarchyController... "
CACHE_CLEARS=$(grep -c "Cache::forget" app/Http/Controllers/Api/CourseHierarchyController.php 2>/dev/null)
if [ "$CACHE_CLEARS" -ge 6 ]; then
    echo -e "${GREEN}PASS${NC} (Found $CACHE_CLEARS cache clears)"
else
    echo -e "${YELLOW}WARNING${NC}"
    echo "   ⚠️  Expected at least 6 cache clears in CourseHierarchyController (found $CACHE_CLEARS)"
    WARNINGS=$((WARNINGS + 1))
fi

# Check 7: Verify student module view exists
echo -n "7. Checking for student module view... "
if [ -f "resources/views/student/courses/module.blade.php" ]; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC}"
    echo "   ❌ resources/views/student/courses/module.blade.php not found"
    ERRORS=$((ERRORS + 1))
fi

# Check 8: Verify route naming in student module view
echo -n "8. Checking route naming in student module view... "
if [ -f "resources/views/student/courses/module.blade.php" ]; then
    CORRECT_ROUTE=$(grep -c "student.courses.modules.subpages.show" resources/views/student/courses/module.blade.php 2>/dev/null)
    if [ "$CORRECT_ROUTE" -gt 0 ]; then
        echo -e "${GREEN}PASS${NC}"
    else
        echo -e "${RED}FAIL${NC}"
        echo "   ❌ Incorrect route name in student module view"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo -e "${YELLOW}SKIP${NC}"
fi

# Check 9: Verify npm packages are installed
echo -n "9. Checking npm packages... "
if [ -d "node_modules" ]; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC}"
    echo "   ❌ node_modules not found"
    echo "   Run: npm install"
    ERRORS=$((ERRORS + 1))
fi

# Check 10: Verify composer packages are installed
echo -n "10. Checking composer packages... "
if [ -d "vendor" ]; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC}"
    echo "   ❌ vendor directory not found"
    echo "   Run: composer install"
    ERRORS=$((ERRORS + 1))
fi

echo ""
echo "=========================================="
echo "Summary"
echo "=========================================="
echo -e "Errors: ${RED}$ERRORS${NC}"
echo -e "Warnings: ${YELLOW}$WARNINGS${NC}"
echo ""

if [ $ERRORS -gt 0 ]; then
    echo -e "${RED}❌ DEPLOYMENT BLOCKED${NC}"
    echo "Please fix the errors above before deploying."
    exit 1
elif [ $WARNINGS -gt 0 ]; then
    echo -e "${YELLOW}⚠️  WARNINGS FOUND${NC}"
    echo "Review warnings above. Deployment can proceed but issues may exist."
    exit 0
else
    echo -e "${GREEN}✅ ALL CHECKS PASSED${NC}"
    echo "Ready for deployment!"
    exit 0
fi
