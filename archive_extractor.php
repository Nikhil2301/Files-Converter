<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZIP/RAR Extractor</title>
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
    <h2>🗜️ ZIP/RAR Extractor</h2>
    <form method="post" enctype="multipart/form-data" action="">
        <input type="file" name="archive_file" accept=".zip,.rar" required>
        <input type="submit" value="Extract Archive">
    </form>
    <a class="back-link" href="index.php">← Back to Home</a>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archive_file'])) {
        $file = $_FILES['archive_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo "<div class='message error'>File upload error.</div>";
        } else {
            $tmpName = $file['tmp_name'];
            $name = $file['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $outputDir = sys_get_temp_dir() . '/archive_' . uniqid();
            mkdir($outputDir);
            if ($ext === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($tmpName) === TRUE) {
                    $zip->extractTo($outputDir);
                    $zip->close();
                    // Zip extracted, re-zip for download
                    $zipOut = $outputDir . '/extracted.zip';
                    $zip2 = new ZipArchive();
                    if ($zip2->open($zipOut, ZipArchive::CREATE) === TRUE) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($outputDir, FilesystemIterator::SKIP_DOTS));
                        foreach ($files as $fileinfo) {
                            if ($fileinfo->isFile() && $fileinfo->getFilename() !== 'extracted.zip') {
                                $zip2->addFile($fileinfo->getPathname(), $fileinfo->getFilename());
                            }
                        }
                        $zip2->close();
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="extracted.zip"');
                        header('Content-Length: ' . filesize($zipOut));
                        readfile($zipOut);
                        unlink($zipOut);
                        // Cleanup
                        foreach (glob($outputDir.'/*') as $f) unlink($f);
                        rmdir($outputDir);
                        exit();
                    } else {
                        echo "<div class='message error'>Could not create output ZIP.</div>";
                    }
                } else {
                    echo "<div class='message error'>Failed to open ZIP archive.</div>";
                }
            } elseif ($ext === 'rar') {
                $unrar = trim(shell_exec('where unrar')); // Windows: 'where', Linux: 'which'
                if ($unrar) {
                    $cmd = escapeshellcmd($unrar) . ' x -o+ ' . escapeshellarg($tmpName) . ' ' . escapeshellarg($outputDir);
                    shell_exec($cmd);
                    // Re-zip for download
                    $zipOut = $outputDir . '/extracted.zip';
                    $zip2 = new ZipArchive();
                    if ($zip2->open($zipOut, ZipArchive::CREATE) === TRUE) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($outputDir, FilesystemIterator::SKIP_DOTS));
                        foreach ($files as $fileinfo) {
                            if ($fileinfo->isFile() && $fileinfo->getFilename() !== 'extracted.zip') {
                                $zip2->addFile($fileinfo->getPathname(), $fileinfo->getFilename());
                            }
                        }
                        $zip2->close();
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="extracted.zip"');
                        header('Content-Length: ' . filesize($zipOut));
                        readfile($zipOut);
                        unlink($zipOut);
                        // Cleanup
                        foreach (glob($outputDir.'/*') as $f) unlink($f);
                        rmdir($outputDir);
                        exit();
                    } else {
                        echo "<div class='message error'>Could not create output ZIP.</div>";
                    }
                } else {
                    echo "<div class='message error'>unrar command not found on server. Please install unrar to extract RAR files.";
                }
            } else {
                echo "<div class='message error'>Unsupported archive type.</div>";
            }
        }
    }
    ?>
</div>
</body>
</html>
