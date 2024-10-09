<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class AttachmentProcessor

{
    //Patterns array 
    protected array $patterns = [
        'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', //email
        'mobile' => '/^((?:[1-9][0-9 ().-]{5,28}[0-9])|(?:(00|0)( ){0,1}[1-9][0-9 ().-]{3,26}[0-9])|(?:(\+)( ){0,1}[1-9][0-9 ().-]{4,27}[0-9]))$/m', //phone
        'domain' => '/(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}/'
    ];
    public function processFile($filePath)
    {
        // Determine the file type by its extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Get the current user (for conditional logic based on user)
        $user = Auth::user();
        if ($user instanceof User && $user->isAdmin()) {
            return false;
        }

        // Check if the file is in the specific directories
        if (strpos($filePath, config('attachment.attachment_path')) !== false || strpos($filePath, config('attachment.inline_attachment_path')) !== false) {
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    // Process image files
                    return $this->processImage($filePath, $user);

                case 'pdf':
                    // Process PDF files
                    return $this->processPDF($filePath, $user);

                case 'txt':
                case 'csv':
                    // Process text or CSV files
                    return $this->processText($filePath, $user);

                default:
                    // Unsupported file type
                    return response('Unsupported file type', 415);  // 415 Unsupported Media Type
            }
        }

        return response('File not found or unsupported directory', 404);  // 404 Not Found
    }

    function processPDF($filePath, $user)
    {
        $pdfPath = storage_path('app/public/' . $filePath);

        // Create an instance of your PdfModifier and process the PDF
        $modifier = new PdfModifier();
        $file = $modifier->processPDF($pdfPath, $this->patterns);
        if ($file) {
            return response($file)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"');
        }
        //Set headers for PDF output
        return false;
    }



    /**
     * Process image files.
     */
    protected function processImage($filePath, $user)
    {
        $imagePath = storage_path('app/public/' . $filePath);

        $data = Ocr::parseImgData(Ocr::createImageData($imagePath), $this->patterns);

        if (count($data) > 0) {
            // OCR successful with maskable data, process the data
            $imgGd = ImageModifier::createImageFromPath($imagePath);

            // Process each part and add blur rectangles
            foreach ($data as $part) {
                $imgGd = ImageModifier::addBlurryRedRectangleWithNoise($imgGd, $part['c']);
            }

            // Get the file extension
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

            // Capture the binary output of the image
            ob_start();

            // Output the image based on its extension (e.g., JPEG, PNG)
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($imgGd);
                    $mimeType = 'image/jpeg';
                    break;
                case 'png':
                    imagepng($imgGd);
                    $mimeType = 'image/png';
                    break;
                case 'gif':
                    imagegif($imgGd);
                    $mimeType = 'image/gif';
                    break;
                default:
                    // Handle unsupported image types
                    return response('Unsupported image type', 415);
            }

            // Get the binary content
            $imageBinary = ob_get_clean();

            // Clean up the GD resource
            imagedestroy($imgGd);

            // Return the binary data as a response with the correct content type
            return response($imageBinary)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . basename($imagePath) . '"');
        }
    }




    /**
     * Process text or CSV files.
     */
    protected function processText($filePath, $user)
    {
        // Text file handling logic (e.g., reading, modifying, etc.)
        if (!Storage::disk('public')->exists($filePath)) {
            return response('File not found', 404);
        }

        return false;
    }
}
