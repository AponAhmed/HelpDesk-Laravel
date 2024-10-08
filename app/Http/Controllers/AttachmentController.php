<?php

namespace App\Http\Controllers;

use App\Services\AttachmentProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    protected $attachmentProcessor;

    public function __construct(AttachmentProcessor $attachmentProcessor)
    {
        $this->attachmentProcessor = $attachmentProcessor;
    }


    public function showInlineAttachment($path)
    {
        $filePath = config('attachment.inline_attachment_path') . "/" . $path;

        if (Storage::disk('public')->exists($filePath)) {
            $file = $this->attachmentProcessor->processFile($filePath);
            if ($file) {
                return $file;  // Serve the processed image
            }
            // Fallback: return the original image if no processing is needed
            return response()->file(storage_path('app/public/' . $filePath));
        }
        abort(404);  // File not found
    }

    public function showAttachment($path)
    {
        // Define the storage path
        $filePath = config('attachment.attachment_path') . "/" . $path;  // or 'inline-attachments/' . $path

        // Check if the file exists in the public storage path
        if (Storage::disk('public')->exists($filePath)) {
            // Process the image using the AttachmentProcessor service
            $file = $this->attachmentProcessor->processFile($filePath);
            if ($file) {
                return $file;  // Serve the processed image
            }
            // Fallback: return the original image if no processing is needed
            return response()->file(storage_path('app/public/' . $filePath));
        }

        abort(404);  // File not found
    }
}
