# laraDesk11
 Email Client Application



# External Libraries and Extensions

This document outlines the necessary external libraries and extensions required for PDF processing and text extraction in a PHP application, specifically for production server setup.


## 1. Mailparse (PHP Extension)
- **Description**: Mailparse is a PHP extension that provides functions for parsing and manipulating email messages.
- **Usage**: Useful for extracting content from emails and analyzing email structure.
- **Key Functions**: `mailparse_msg_create()`, `mailparse_msg_parse_file()`, etc.

### Installation Instructions for Production Server
1. **Enable Mailparse in PHP**:
   - Install the Mailparse PHP extension via your package manager, or manually compile it if needed:
     ```bash
     sudo apt-get install php-mailparse
     ```
   - Ensure that it is enabled by checking your `php.ini` file or using:
     ```bash
     php -m | grep mailparse
     ```

2. **Restart your web server** (Apache/Nginx) to apply changes:
   ```bash
   sudo service apache2 restart
   # or
   sudo service nginx restart
   ```


## 2. Tesseract-OCR (PHP OCR Library)
- **Description**: Tesseract is an optical character recognition (OCR) engine that can recognize text from images.
- **Usage**: Used to extract text and coordinates from images generated from the PDF pages.
- **Key Class**: `thiagoalessio\TesseractOCR\TesseractOCR`

### Installation Instructions for Production Server
1. **Install Tesseract**:
   - Use your server's package manager to install Tesseract. For example, on Ubuntu, you can run:
     ```bash
     sudo apt-get install tesseract-ocr
     ```

2. **Install TesseractOCR PHP Library via Composer**:
   ```bash
   composer require thiagoalessio/tesseract_ocr
   ```

### Basic Usage
```php
use thiagoalessio\TesseractOCR\TesseractOCR;

$ocr = new TesseractOCR('path/to/image.png');
$text = $ocr->run();  // Extract text from the image
```

## 3. Imagick (PHP Extension)
- **Description**: Imagick is a PHP extension that provides an interface to the ImageMagick library, allowing for the creation, modification, and conversion of images.
- **Usage**: Required for handling image conversion from PDF to images.
- **Key Class**: `\Imagick`
- **Dependencies**:
  - **ImageMagick**: Must be installed on your server.
  - **Ghostscript**: Required by Imagick for PDF handling.

### Installation Instructions for Production Server
1. **Install ImageMagick**:
   - Use your server's package manager to install ImageMagick. For example, on Ubuntu, you can run:
     ```bash
     sudo apt-get install imagemagick
     ```
   - Ensure that the installation includes Ghostscript support:
     ```bash
     sudo apt-get install ghostscript
     ```

2. **Enable Imagick in PHP**:
   - Find and install the Imagick PHP extension via your package manager, or manually compile it if needed:
     ```bash
     sudo apt-get install php-imagick
     ```
   - After installation, ensure that it is enabled by checking your `php.ini` file or using:
     ```bash
     php -m | grep imagick
     ```

3. **Restart your web server** (Apache/Nginx) to apply changes:
   ```bash
   sudo service apache2 restart
   # or
   sudo service nginx restart
   ```



## Summary
- **Imagick**: For converting PDF pages into images.
- **Tesseract-OCR**: For extracting text from the generated images.
- **Mailparse**: For parsing and manipulating email messages.

Make sure to install and configure these dependencies correctly on your production server to ensure smooth functionality of your PDF processing application.
