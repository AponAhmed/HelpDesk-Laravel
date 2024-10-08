<?php

namespace App\Services;

class ImageModifier
{
    static function addBlurryRedRectangleWithNoise($image, $coordinates)
    {
        $startX = $coordinates['x1'];
        $startY = $coordinates['y1'];
        $endX = $coordinates['x2'];
        $endY = $coordinates['y2'];

        // Ensure the coordinates are in the right order
        $startX = max(0, $startX);
        $startY = max(0, $startY);
        $endX = min(imagesx($image), $endX);
        $endY = min(imagesy($image), $endY);

        // Create a semi-transparent red color
        // $red = imagecolorallocatealpha($image, 255, 0, 0, 60); // 60 for some transparency

        // // Fill the rectangle with semi-transparent red color
        // imagefilledrectangle($image, $startX, $startY, $endX, $endY, $red);

        // Create noise overlay
        $noiseImage = self::createNoiseImage($endX - $startX, $endY - $startY);

        // Blend the noise with the rectangle
        imagecopy($image, $noiseImage, $startX, $startY, 0, 0, imagesx($noiseImage), imagesy($noiseImage));

        // Destroy the noise image to free memory
        imagedestroy($noiseImage);

        return $image;
    }

    static function createImageFromPath($imagePath)
    {
        // Check if the file exists
        if (!file_exists($imagePath)) {
            return "File does not exist.";
        }

        // Get the file extension
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        // Create the GD image resource based on the file extension
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($imagePath);
            case 'png':
                return imagecreatefrompng($imagePath);
            case 'gif':
                return imagecreatefromgif($imagePath);
            case 'webp':
                return imagecreatefromwebp($imagePath);
            default:
                return "Unsupported image type.";
        }
    }


    static function createNoiseImage($width, $height)
    {
        // Create a new image for the noise
        $noiseImage = imagecreatetruecolor($width, $height);

        // Enable alpha blending
        imagealphablending($noiseImage, true);

        // Save the alpha channel information
        imagesavealpha($noiseImage, true);

        // Fill with transparent color
        $transparent = imagecolorallocatealpha($noiseImage, 255, 255, 255, 127);
        imagefill($noiseImage, 0, 0, $transparent);

        // Generate noise
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                // Random noise value
                $randColor = rand(220, 255);
                $color = imagecolorallocate($noiseImage, $randColor, $randColor, $randColor); // Grayscale noise
                imagesetpixel($noiseImage, $x, $y, $color);
            }
        }

        return $noiseImage;
    }
}
