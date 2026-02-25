<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all submissions with file_paths
        $submissions = DB::table('submissions')
            ->whereNotNull('file_paths')
            ->get();

        foreach ($submissions as $submission) {
            $filePaths = json_decode($submission->file_paths, true);
            
            if (is_array($filePaths) && !empty($filePaths)) {
                foreach ($filePaths as $filePath) {
                    // Skip if not a valid file path
                    if (empty($filePath) || !is_string($filePath)) {
                        continue;
                    }

                    // Extract file information
                    $fileName = basename($filePath);
                    $fileSize = 0;
                    $mimeType = 'application/octet-stream';

                    // Try to get file size and mime type if file exists
                    if (Storage::disk('local')->exists($filePath)) {
                        $fileSize = Storage::disk('local')->size($filePath);
                        $mimeType = Storage::disk('local')->mimeType($filePath) ?? 'application/octet-stream';
                    }

                    // Create submission_files record
                    DB::table('submission_files')->insert([
                        'submission_id' => $submission->id,
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                        'uploaded_at' => $submission->submitted_at ?? $submission->created_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all submission_files records
        DB::table('submission_files')->truncate();
        
        // Note: We don't restore the JSON file_paths as they should still exist
        // in the submissions table for backward compatibility
    }
};
