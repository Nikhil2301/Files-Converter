<?php
// Include Dompdf Autoloader
// Adjust the path if your dompdf folder is not directly in the project root
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['html_file'])) {
    $file = $_FILES['html_file'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error.';
        header('Location: index.php?message=' . urlencode($errorMessage) . '&type=error');
        exit();
    }

    // Validate file type (basic check)
    $fileType = mime_content_type($file['tmp_name']); // More reliable than $file['type']
    $allowedMimeTypes = ['text/html'];

    if (!in_array($fileType, $allowedMimeTypes)) {
        header('Location: index.php?message=' . urlencode('Invalid file type. Please upload an HTML file (.html or .htm).') . '&type=error');
        exit();
    }

    // Get the content of the uploaded HTML file
    $htmlContent = file_get_contents($file['tmp_name']);

    if ($htmlContent === false) {
        header('Location: index.php?message=' . urlencode('Could not read the content of the uploaded HTML file.') . '&type=error');
        exit();
    }

    // Set up Dompdf options (optional but recommended)
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parsing
    $options->set('isRemoteEnabled', true);      // Enable loading remote assets (images, CSS, etc.) - use with caution for security
    // Add more options as needed, e.g., 'defaultFont', 'defaultPaperSize', etc.

    // Initialize Dompdf
    $dompdf = new Dompdf($options);

    // Load HTML content
    $dompdf->loadHtml($htmlContent);

    // (Optional) Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Get the filename without extension for the PDF
    $originalFileName = pathinfo($file['name'], PATHINFO_FILENAME);
    $pdfFileName = $originalFileName . '.pdf';

    // Output the generated PDF (for download)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdfFileName . '"');
    header('Content-Length: ' . strlen($dompdf->output())); // Correct content length

    echo $dompdf->output();
    exit();

} else {
    // If accessed directly without POST request or file
    header('Location: index.php?message=' . urlencode('Please upload an HTML file using the form.') . '&type=error');
    exit();
}
?>