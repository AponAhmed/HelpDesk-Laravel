<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use thiagoalessio\TesseractOCR\TesseractOCR;


class Ocr
{

    public static function createImageData($imageFile)
    {
        $datafile = "";
        try {
            $tesseract = new TesseractOCR($imageFile);
            $tesseract->tempDir(storage_path('framework/temp'));
            //$tesseract->setOutputFile(dirname(__FILE__) . "/output.text");
            $tesseract->config('tessedit_create_hocr', '1'); // Enable hOCR mode
            // Run Tesseract and capture the hOCR output
            $datafile = str_replace(".txt", ".hocr", $tesseract->command->getOutputFile());
            $tesseract->run();

            //$tesseract->cleanTempFiles();
        } catch (Exception $e) {
            // echo $e->getMessage();
        }
        return $datafile;
    }


    private static function checkMatches($text, $x1, $y1, $x2, $y2, $patterns = [])
    {
        $markedData = [];

        foreach ($patterns as  $k => $pattern) {
            if (preg_match($pattern, $text, $Match)) {
                $markedData[] = [
                    'c' => [
                        'x1' => $x1,
                        'y1' => $y1,
                        'x2' => $x2,
                        'y2' => $y2,
                    ],
                    'text' => $Match[0],
                    'type' => $k
                ];
            }
        }

        return $markedData;
    }


    static function parseImgData($datafile, $patterns = [])
    {
        $MarkedData = [];
        $hocrOutput = file_get_contents($datafile);


        // Find text and bounding box using regex
        // Create a new DOMDocument instance
        $dom = new DOMDocument;
        // Load the HTML into the DOMDocument (suppress errors due to invalid HTML)
        libxml_use_internal_errors(true); // Suppress errors
        $dom->loadHTML($hocrOutput);
        libxml_clear_errors();

        // Find all span elements with the class 'ocr_line'
        $xpath = new DOMXPath($dom);
        $ocrLineNodes = $xpath->query("//span[contains(@class, 'ocr_line')]");

        // Output the matched spans
        foreach ($ocrLineNodes as $node) {

            $title = "";
            // Get the title attribute
            if ($node instanceof DOMElement) {
                // Get the title attribute
                $title = $node->getAttribute('title');
            }

            if (preg_match('/bbox (\d+) (\d+) (\d+) (\d+)/', $title, $matches)) {
                $x1 = $matches[1]; // X1 coordinate
                $y1 = $matches[2]; // Y1 coordinate
                $x2 = $matches[3]; // X2 coordinate
                $y2 = $matches[4]; // Y2 coordinate


                // Get inner HTML and inner text
                $innerHTML = $dom->saveHTML($node); // Inner HTML
                $innerText = trim(preg_replace('/\s+/', ' ', $node->textContent)); // Inner Tex

                $lineData = self::checkMatches($innerText, $x1, $y1, $x2, $y2, $patterns);
                if (isset($lineData[0]) && strlen($lineData[0]['text']) != strlen($innerText)) {
                    $lineData = []; // Full Text of line not matched 
                }
                if (!empty($lineData)) {
                    $MarkedData = array_merge($MarkedData, $lineData);
                } else {
                    //Childs
                    $pattern = '/bbox (\d+) (\d+) (\d+) (\d+);.*>(.*?)<\/span>/';
                    preg_match_all($pattern, $innerHTML, $matches, PREG_SET_ORDER);

                    foreach ($matches as $match) {
                        $x1 = $match[1]; // Left coordinate
                        $y1 = $match[2]; // Top coordinate
                        $x2 = $match[3]; // Right coordinate
                        $y2 = $match[4]; // Bottom coordinate
                        $text = $match[5]; // Recognized text

                        $Data = self::checkMatches($text, $x1, $y1, $x2, $y2, $patterns);
                        if (!empty($Data)) {
                            $MarkedData = array_merge($MarkedData, $Data);
                        }
                    }
                }
            }
        }
        unlink($datafile);
        return $MarkedData;
    }
}
