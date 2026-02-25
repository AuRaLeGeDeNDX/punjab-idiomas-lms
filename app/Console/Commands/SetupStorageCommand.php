<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up storage directories for file management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up storage directories...');

        // Create protected storage directory
        $protectedPath = storage_path('app/protected');
        if (!File::exists($protectedPath)) {
            File::makeDirectory($protectedPath, 0755, true);
            $this->info('Created protected storage directory: ' . $protectedPath);
        } else {
            $this->info('Protected storage directory already exists: ' . $protectedPath);
        }

        // Create subdirectories for organization
        $subdirectories = [
            'protected/courses',
            'protected/users',
            'public/courses',
            'public/users',
            'private/courses',
            'private/users',
        ];

        foreach ($subdirectories as $dir) {
            $fullPath = storage_path('app/' . $dir);
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
                $this->info('Created directory: ' . $fullPath);
            }
        }

        // Create .gitignore files to preserve directory structure
        $gitignoreContent = "*\n!.gitignore\n";
        
        foreach ($subdirectories as $dir) {
            $gitignorePath = storage_path('app/' . $dir . '/.gitignore');
            if (!File::exists($gitignorePath)) {
                File::put($gitignorePath, $gitignoreContent);
                $this->info('Created .gitignore in: ' . dirname($gitignorePath));
            }
        }

        // Set proper permissions
        $this->info('Setting directory permissions...');
        
        try {
            // Set permissions for storage directories
            File::chmod(storage_path('app/protected'), 0755);
            File::chmod(storage_path('app/public'), 0755);
            File::chmod(storage_path('app/private'), 0755);
            
            $this->info('Directory permissions set successfully.');
        } catch (\Exception $e) {
            $this->warn('Could not set directory permissions: ' . $e->getMessage());
        }

        $this->info('Storage setup completed successfully!');
        
        // Display storage information
        $this->displayStorageInfo();
    }

    /**
     * Display storage configuration information
     */
    private function displayStorageInfo()
    {
        $this->newLine();
        $this->info('Storage Configuration:');
        $this->table(
            ['Disk', 'Driver', 'Root Path', 'Visibility'],
            [
                ['local', 'local', storage_path('app/private'), 'private'],
                ['public', 'local', storage_path('app/public'), 'public'],
                ['protected', 'local', storage_path('app/protected'), 'private'],
            ]
        );

        $this->newLine();
        $this->info('File Upload Limits:');
        $this->line('- Maximum file size: 50MB');
        $this->line('- Supported file types: PDF, DOC, DOCX, TXT, JPG, PNG, GIF, MP3, MP4, ZIP, etc.');
        $this->line('- Malware scanning: Basic signature detection enabled');
        $this->line('- File versioning: Supported');
        $this->line('- Temporary URLs: 60 minutes default expiration');

        $this->newLine();
        $this->info('Security Features:');
        $this->line('- Access control based on user roles and course enrollment');
        $this->line('- Secure file serving with temporary URLs');
        $this->line('- File type validation and MIME type checking');
        $this->line('- File deduplication using SHA-256 hashing');
    }
}