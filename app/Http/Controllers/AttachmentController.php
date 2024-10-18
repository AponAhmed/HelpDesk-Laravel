<?php

namespace App\Http\Controllers;

use App\Services\AttachmentProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $processedFilePath = config('attachment.filtered_attachment_path') . "/" . $path;
        if (Storage::disk('public')->exists($processedFilePath) && !Auth::user()->isAdmin()) { //if Already Processed File
            return response()->file(storage_path('app/public/' . $processedFilePath));
        } else {
            if (Storage::disk('public')->exists($filePath)) {
                $file = $this->attachmentProcessor->processFile($filePath);
                if ($file) {
                    return response()->file(storage_path('app/public/' . $file));
                }
                // Fallback: return the original image if no processing is needed
                return response()->file(storage_path('app/public/' . $filePath));
            }
        }

        abort(404);  // File not found
    }

    public function showAttachment($path)
    {
        // Define the storage path
        $filePath = config('attachment.attachment_path') . "/" . $path;  // or 'inline-attachments/' . $path
        $processedFilePath = config('attachment.filtered_attachment_path') . "/" . $path;

        //if Already files has been Processed
        if (Storage::disk('public')->exists($processedFilePath) && !Auth::user()->isAdmin()) {
            return response()->file(storage_path('app/public/' . $processedFilePath));
        } else {
            // Check if the file exists in the public storage path
            if (Storage::disk('public')->exists($filePath)) {
                // Process the image using the AttachmentProcessor service
                $file = $this->attachmentProcessor->processFile($filePath);
                if ($file) {
                    return response()->file(storage_path('app/public/' . $file));
                }
                // Fallback: return the original image if no processing is needed
                return response()->file(storage_path('app/public/' . $filePath));
            }
        }

        abort(404);  // File not found
    }
}
