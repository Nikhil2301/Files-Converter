<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PDF to JPG/PNG Converter</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { background: #fff; padding: 32px; border-radius: 10px; box-shadow: 0 0 12px rgba(0,0,0,0.10); max-width: 480px; margin: 40px auto; }
        h2 { text-align: center; color: #1976d2; margin-bottom: 28px; }
        form { display: flex; flex-direction: column; gap: 18px; }
        input[type="file"] { padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; background: #fafbfc; font-size: 1rem; }
        input[type="submit"] { background: linear-gradient(90deg,#1976d2,#42a5f5); color: #fff; border: none; border-radius: 6px; padding: 14px 0; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        input[type="submit"]:hover { background: linear-gradient(90deg,#1565c0,#1976d2); }
        .back-link { display: block; margin: 18px 0 0 0; text-align: center; color: #1976d2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        .message { margin-top: 18px; padding: 12px; border-radius: 5px; text-align: center; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        @media (max-width: 600px) { .container { padding: 14px; } }
    </style>
</head>
<body>
<div class="container">
    <h2>📄→🖼️ PDF to JPG/PNG</h2>
    <form method="post" enctype="multipart/form-data" action="">
        <input type="file" name="pdf_file" accept="application/pdf" required>
        <input type="submit" value="Convert to Image(s)">
    </form>
    <a class="back-link" href="index.php">← Back to Home</a>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
        if (!extension_loaded('imagick')) {
            echo "<div class='message error'>Imagick extension is not installed on this server.</div>";
        } else {
            $file = $_FILES['pdf_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo "<div class='message error'>File upload error.</div>";
            } else {
                $tmpName = $file['tmp_name'];
                $outputDir = sys_get_temp_dir() . '/pdf2img_' . uniqid();
                mkdir($outputDir);
                $images = [];
                try {
                    $imagick = new Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($tmpName);
                    foreach ($imagick as $i => $img) {
                        $img->setImageFormat('jpg');
                        $imgPath = "$outputDir/page_" . ($i+1) . ".jpg";
                        $img->writeImage($imgPath);
                        $images[] = $imgPath;
                    }
                    $imagick->clear();
                    $imagick->destroy();
                    if (count($images) > 0) {
                        $zipPath = $outputDir . '/images.zip';
                        $zip = new ZipArchive();
                        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                            foreach ($images as $img) {
                                $zip->addFile($img, basename($img));
                            }
                            $zip->close();
                            echo "<div class='message success'>Conversion successful! <a href='" . basename($zipPath) . "' download>Download ZIP</a></div>";
                            // Serve the zip for download and cleanup
                            header('Content-Type: application/zip');
                            header('Content-Disposition: attachment; filename="images.zip"');
                            header('Content-Length: ' . filesize($zipPath));
                            readfile($zipPath);
                            // Cleanup
                            foreach ($images as $img) unlink($img);
                            unlink($zipPath);
                            rmdir($outputDir);
                            exit();
                        } else {
                            echo "<div class='message error'>Could not create ZIP file.</div>";
                        }
                    } else {
                        echo "<div class='message error'>No images were generated from the PDF.</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='message error'>Conversion failed: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
        }
    }
    ?>
</div>
</body>
</html>
