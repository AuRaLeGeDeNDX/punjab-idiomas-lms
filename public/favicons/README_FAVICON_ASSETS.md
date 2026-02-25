# Punjab Idiomas Favicon Assets

## Required Favicon Files

This directory should contain the following Punjab Idiomas favicon files in multiple sizes:

### 1. Favicon 16x16
- **Filename**: `favicon-16x16.png`
- **Size**: 16x16 pixels (exact)
- **Usage**: Browser tabs, bookmarks (small displays)
- **Format**: PNG

### 2. Favicon 32x32
- **Filename**: `favicon-32x32.png`
- **Size**: 32x32 pixels (exact)
- **Usage**: Browser tabs, bookmarks (standard displays)
- **Format**: PNG

### 3. Favicon 48x48
- **Filename**: `favicon-48x48.png`
- **Size**: 48x48 pixels (exact)
- **Usage**: Browser tabs, bookmarks (high-DPI displays)
- **Format**: PNG

### 4. Main Favicon (ICO format)
- **Location**: `public/favicon.ico` (parent directory)
- **Size**: 32x32 pixels (or multi-size ICO)
- **Usage**: Default favicon for browsers
- **Format**: ICO (can contain multiple sizes)

## How to Add Your Favicon Assets

1. **Prepare your favicon** in the Punjab Idiomas branding style
2. **Create multiple sizes** (16x16, 32x32, 48x48)
3. **Name them exactly** as shown above (case-sensitive)
4. **Copy PNG files** to this directory (`public/favicons/`)
5. **Copy ICO file** to `public/favicon.ico`
6. **Verify accessibility** by visiting:
   - `http://your-domain.com/favicons/favicon-16x16.png`
   - `http://your-domain.com/favicons/favicon-32x32.png`
   - `http://your-domain.com/favicons/favicon-48x48.png`
   - `http://your-domain.com/favicon.ico`

## Design Guidelines

- **Simple Design**: Favicons should be simple and recognizable at small sizes
- **Square Format**: All favicons must be perfect squares
- **Color Mode**: RGB color mode
- **Background**: Consider both light and dark browser themes
- **Consistency**: Should match the Punjab Idiomas logo style

## Creating Favicons from Logo

If you have a logo but need to create favicons:

1. **Use online tools** like:
   - https://realfavicongenerator.net/
   - https://favicon.io/
   - https://www.favicon-generator.org/

2. **Or use image editing software**:
   - Resize your logo to square format
   - Export at 16x16, 32x32, and 48x48 sizes
   - Create ICO file with multiple sizes embedded

## Current Status

⚠️ **PLACEHOLDER FILES NEEDED**: This directory structure has been created, but actual favicon image files need to be added by the system administrator.

Until actual favicon files are provided:
- Browsers will use the existing `public/favicon.ico` (if present)
- Or display a default browser icon
- The application will continue to function normally

## Testing After Upload

After adding your favicon files:

1. **Clear browser cache** (hard refresh: Ctrl+Shift+R or Cmd+Shift+R)

2. **Clear Laravel caches**:
   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

3. **Verify favicon appears** in:
   - Browser tabs
   - Bookmarks
   - Browser history
   - Desktop shortcuts

4. **Test on multiple browsers**:
   - Chrome/Edge
   - Firefox
   - Safari
   - Mobile browsers

## HTML Implementation

The favicon links are implemented in `resources/views/layouts/app.blade.php`:

```html
<!-- Main Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

<!-- PNG Variants -->
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicons/favicon-48x48.png') }}">
```

## Related Files

- Logo files: `public/images/` directory
- Main layout: `resources/views/layouts/app.blade.php`
- Configuration: `config/app.php` and `.env` file

## Support

For questions about favicon requirements, refer to:
- `.kiro/specs/punjab-idiomas-rebranding/requirements.md` (Requirement 5)
- `.kiro/specs/punjab-idiomas-rebranding/design.md`
