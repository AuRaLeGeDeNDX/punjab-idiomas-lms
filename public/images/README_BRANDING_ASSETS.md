# Punjab Idiomas Branding Assets

## Required Logo Files

This directory should contain the following Punjab Idiomas logo files:

### 1. Main Logo
- **Filename**: `punjab-idiomas-logo.png`
- **Recommended Size**: 200x60px (or similar aspect ratio)
- **Usage**: Main navigation bar, desktop views, general branding
- **Format**: PNG with transparent background preferred

### 2. Mobile Logo
- **Filename**: `punjab-idiomas-logo-mobile.png`
- **Recommended Size**: 40x40px (square format)
- **Usage**: Mobile navigation, small screen displays
- **Format**: PNG with transparent background preferred

### 3. Small Logo
- **Filename**: `punjab-idiomas-logo-small.png`
- **Recommended Size**: 32x32px (square format)
- **Usage**: Compact displays, icons, thumbnails
- **Format**: PNG with transparent background preferred

## How to Add Your Branding Assets

1. **Prepare your logo files** in the sizes specified above
2. **Name them exactly** as shown (case-sensitive)
3. **Copy them** to this directory (`public/images/`)
4. **Verify accessibility** by visiting:
   - `http://your-domain.com/images/punjab-idiomas-logo.png`
   - `http://your-domain.com/images/punjab-idiomas-logo-mobile.png`
   - `http://your-domain.com/images/punjab-idiomas-logo-small.png`

## Design Guidelines

- **File Format**: PNG is recommended for logos with transparency
- **Color Mode**: RGB color mode
- **Background**: Transparent background preferred for flexibility
- **Quality**: High-resolution images that scale well
- **Aspect Ratio**: Maintain consistent aspect ratios across variants

## Current Status

⚠️ **PLACEHOLDER FILES NEEDED**: This directory structure has been created, but actual branding image files need to be added by the system administrator.

Until actual logo files are provided, the application will:
- Display fallback text-based branding ("Punjab Idiomas")
- Log warnings about missing logo files
- Continue to function normally

## Testing After Upload

After adding your logo files, test them by:

1. **Clear Laravel caches**:
   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

2. **Visit the application** and verify logos appear in:
   - Main navigation bar
   - Login page
   - Welcome page
   - All user role pages (Admin, Teacher, Student)

3. **Test responsive display** on:
   - Desktop (>1024px width)
   - Tablet (768px-1024px width)
   - Mobile (<768px width)

## Related Files

- Favicon files: `public/favicons/` directory
- Main favicon: `public/favicon.ico`
- Configuration: `config/app.php` and `.env` file
- Templates: `resources/views/layouts/app.blade.php` and other view files

## Support

For questions about branding asset requirements, refer to:
- `.kiro/specs/punjab-idiomas-rebranding/requirements.md`
- `.kiro/specs/punjab-idiomas-rebranding/design.md`
