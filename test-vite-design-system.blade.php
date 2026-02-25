<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design System Vite Test</title>
    @vite(['resources/css/design-system.css'])
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            padding: 2rem;
            background-color: var(--bg-subtle);
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .test-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--card-padding);
            margin-bottom: var(--section-margin);
            box-shadow: var(--shadow-sm);
        }
        .test-card h2 {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .color-demo {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        .color-box {
            padding: 1rem;
            border-radius: var(--radius-md);
            text-align: center;
            color: white;
            font-weight: var(--font-weight-medium);
        }
        .status-success {
            background-color: var(--admin-success);
        }
        .status-warning {
            background-color: var(--admin-warning);
        }
        .status-danger {
            background-color: var(--admin-danger);
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 style="color: var(--text-primary); font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); margin-bottom: 2rem;">
            Design System @vite Directive Test
        </h1>
        
        <div class="test-card">
            <h2>✓ CSS Variables Loaded Successfully</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                If you can see this card with proper styling, the design-system.css file is loading correctly via the @vite directive.
            </p>
            
            <div class="color-demo">
                <div class="color-box status-success">Success</div>
                <div class="color-box status-warning">Warning</div>
                <div class="color-box status-danger">Danger</div>
            </div>
        </div>
        
        <div class="test-card">
            <h2>Verification Checklist</h2>
            <ul style="color: var(--text-secondary); line-height: 1.8;">
                <li>✓ Build process completed without errors</li>
                <li>✓ Compiled file exists in public/build/assets/design-system-*.css</li>
                <li>✓ File is registered in Vite manifest</li>
                <li>✓ CSS can be loaded in Blade templates using @vite directive</li>
            </ul>
        </div>
        
        <div class="test-card">
            <h2>CSS Custom Properties Test</h2>
            <div style="display: grid; gap: 0.5rem; color: var(--text-secondary); font-size: var(--font-size-sm);">
                <div>Primary Color: <span style="color: var(--admin-primary); font-weight: var(--font-weight-semibold);">var(--admin-primary)</span></div>
                <div>Success Color: <span style="color: var(--admin-success); font-weight: var(--font-weight-semibold);">var(--admin-success)</span></div>
                <div>Warning Color: <span style="color: var(--admin-warning); font-weight: var(--font-weight-semibold);">var(--admin-warning)</span></div>
                <div>Danger Color: <span style="color: var(--admin-danger); font-weight: var(--font-weight-semibold);">var(--admin-danger)</span></div>
                <div>Info Color: <span style="color: var(--admin-info); font-weight: var(--font-weight-semibold);">var(--admin-info)</span></div>
                <div>Secondary Color: <span style="color: var(--admin-secondary); font-weight: var(--font-weight-semibold);">var(--admin-secondary)</span></div>
            </div>
        </div>
    </div>
</body>
</html>
