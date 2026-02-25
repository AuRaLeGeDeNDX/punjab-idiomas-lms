# Deployment Scripts

This directory contains scripts to ensure code quality and prevent common deployment issues.

## Pre-Deployment Check

The pre-deployment check script validates that all critical requirements are met before deploying code.

### Usage

**Linux/Mac:**
```bash
chmod +x scripts/pre-deployment-check.sh
./scripts/pre-deployment-check.sh
```

**Windows:**
```cmd
scripts\pre-deployment-check.bat
```

### What It Checks

1. ✅ **No `public/hot` file** - Prevents Vite dev server dependency
2. ✅ **Build manifest exists** - Ensures assets are built
3. ✅ **No hardcoded Vite URLs** - Prevents dev server dependencies
4. ✅ **@vite directives used** - Ensures proper asset loading
5. ✅ **Cache clearing in ModuleController** - Prevents stale data
6. ✅ **Cache clearing in CourseHierarchyController** - Prevents stale data
7. ✅ **Student module view exists** - Prevents 404 errors
8. ✅ **Correct route naming** - Prevents route not found errors
9. ✅ **npm packages installed** - Ensures dependencies are present
10. ✅ **Composer packages installed** - Ensures dependencies are present

### Exit Codes

- `0` - All checks passed or only warnings
- `1` - Critical errors found, deployment blocked

### Integration with CI/CD

Add to your CI/CD pipeline:

**GitHub Actions:**
```yaml
- name: Run pre-deployment checks
  run: ./scripts/pre-deployment-check.sh
```

**GitLab CI:**
```yaml
pre-deployment-check:
  script:
    - chmod +x scripts/pre-deployment-check.sh
    - ./scripts/pre-deployment-check.sh
```

## Quick Deployment Commands

### Development
```bash
# Build assets
npm run build

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run checks
./scripts/pre-deployment-check.sh
```

### Production
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build assets
npm run build

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Run checks
./scripts/pre-deployment-check.sh
```

## Troubleshooting

### Script Permission Denied (Linux/Mac)
```bash
chmod +x scripts/pre-deployment-check.sh
```

### Script Not Found (Windows)
Make sure you're in the project root directory:
```cmd
cd C:\path\to\project
scripts\pre-deployment-check.bat
```

### False Positives
If the script reports errors but you believe they're incorrect:
1. Manually verify the check
2. Review the relevant section in `VITE_AND_CACHE_MANAGEMENT_GUIDE.md`
3. Update the script if needed

## Adding New Checks

To add a new check to the script:

1. Add the check logic to both `.sh` and `.bat` files
2. Increment the check number
3. Update this README with the new check
4. Test on both Linux/Mac and Windows

## Related Documentation

- `../VITE_AND_CACHE_MANAGEMENT_GUIDE.md` - Comprehensive guide on Vite and cache management
- `../.gitignore` - Ensures `public/hot` is never committed
