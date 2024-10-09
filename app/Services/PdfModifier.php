<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Smalot\PdfParser\Parser as PdfParser;


class PdfModifier
{
    protected $pdfParser;
    protected $suspiciousContents = [];
    protected $hasSuspectedContents = false;
    protected $coordinates = [];

    private $patterns;

    public function __construct()
    {
        $this->pdfParser = new PdfParser();
    }

    /**
     * Process PDF files and mask sensitive text.
     */
    public function processPDF($filePath, array $maskPatterns)
    {
        $this->patterns = $maskPatterns;
        if (!file_exists($filePath)) {
            return "File not found.";
        }

        // Step 1: Parse PDF text and find suspicious content
        $this->findSuspiciousContent($filePath, $maskPatterns);
        if (!$this->hasSuspectedContents) {
            return false;
        }
        return $this->reMakeByImage($filePath);
        // Step 2: Convert pages to images, get coordinates of suspicious content
        //$this->findTextCoordinates($filePath);

        //dd($this);
        // Step 3: Mask the suspicious text on the PDF pages
        //return $this->maskContent($filePath);
    }


    /**
     * Step 1: Parse PDF and find suspicious content based on the given patterns.
     */
    protected function findSuspiciousContent($filePath, array $maskPatterns)
    {
        $pdf = $this->pdfParser->parseFile($filePath);
        $pages = $pdf->getPages();

        // Iterate through each page to find suspicious content
        foreach ($pages as $pageNumber => $page) {
            $textElements = $page->getText(); // Extract text content from the page

            // Find matches for suspicious content based on the patterns
            foreach ($maskPatterns as $pattern) {
                if (preg_match_all($pattern, $textElements, $matches)) {
                    $this->hasSuspectedContents = true;
                    break;
                    //$this->suspiciousContents[$pageNumber] = $matches[0]; // Store matches per page
                }
            }
        }
    }

    function reMakeByImage($filePath)
    {
        $tempDir = storage_path('framework/temp');

        // Ensure the temp directory exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true); // Create the temp directory if it doesn't exist
        }

        // Initialize Imagick
        $imagick = new \Imagick();
        $imagick->setResolution(120, 120); // Set resolution for better image quality
        $imagick->readImage($filePath); // Load the PDF file into Imagick

        // Create a new Imagick object for the new PDF
        $newPdf = new \Imagick();

        // Process each page in the PDF
        foreach ($imagick as $index => $page) {
            // Convert the current page to an image (e.g., JPG)
            $page->setImageFormat('jpg'); // Set the image format (JPG, PNG, etc.)

            // Create a unique temporary file path for this page image
            $imageFile = $tempDir . '/output_page_' . $index . '.jpg';
            $page->writeImage($imageFile); // Save the current page as an image

            // Read the saved image back into Imagick to add to the new PDF
            $newPage = new \Imagick($imageFile);
            $newPdf->addImage($newPage); // Add the image to the new PDF

            // Cleanup temporary image file
            unlink($imageFile); // Remove the temporary image file
        }

        // Set the format of the new PDF
        $newPdf->setImageFormat('pdf');

        // Create a temporary output path for the new PDF
        $outputFilePath = $tempDir . '/' . time() . '.pdf';
        $newPdf->writeImages($outputFilePath, true); // Save the new PDF

        // Get the binary contents of the new PDF
        $pdfBinary = file_get_contents($outputFilePath);

        // Remove the newly created PDF file after reading its contents
        unlink($outputFilePath);

        // Clean up the Imagick objects
        $imagick->clear();
        $newPdf->clear();

        return $pdfBinary; // Return the binary content of the new PDF
    }

    /**
     * Step 2: Convert PDF pages to images and use Tesseract to get coordinates.
     */
    protected function findTextCoordinates($filePath)
    {
        // Define the temp directory for storing the temporary image files
        $tempDir = storage_path('framework/temp');

        // Ensure the temp directory exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true); // Create the temp directory if it doesn't exist
        }

        // Initialize Imagick
        $imagick = new \Imagick();
        $imagick->setResolution(120, 120);
        $imagick->readImage($filePath); // Load the PDF file into Imagick

        // Process each page in the PDF for suspicious content
        foreach ($this->suspiciousContents as $pageNumber => $texts) {

            $imagick->setIteratorIndex($pageNumber); // Focus on the current page

            // Convert the current page to an image (PNG format)
            $imagick->setImageFormat('jpg');
            $imagick->setImageCompressionQuality(90); // Set to 90 for high quality

            // Create a unique temporary file path for this page image
            $imageFile = $tempDir . '/output_page_' . $pageNumber . '.jpg';
            $imagick->writeImage($imageFile); // Save the page as a PNG image in the temp directory

            // Use TesseractOCR to extract the coordinates of the suspicious text
            $this->coordinates[$pageNumber] = $this->getTextCoordinates($imageFile, $texts);

            // Remove the temporary image file after extracting coordinates
            if (file_exists($imageFile)) {
                unlink($imageFile);
            }
        }

        // Clean up Imagick resources after processing
        $imagick->clear();
        $imagick->destroy();
    }


    /**
     * Use TesseractOCR to get coordinates of the suspicious text.
     */
    protected function getTextCoordinates($imageFile, $texts)
    {
        $data = Ocr::parseImgData(Ocr::createImageData($imageFile), $this->patterns);
        return $data;
    }
}
