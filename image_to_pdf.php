<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image to PDF Converter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f4f4;
        }
        .container {
            background: #fff;
            padding: 32px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.10);
            max-width: 480px;
            margin: 40px auto;
        }
        h2 {
            text-align: center;
            color: #1976d2;
            margin-bottom: 28px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        input[type="file"] {
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background: #fafbfc;
            font-size: 1rem;
        }
        input[type="submit"] {
            background: linear-gradient(90deg, #1976d2, #42a5f5);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 14px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        input[type="submit"]:hover {
            background: linear-gradient(90deg, #1565c0, #1976d2);
        }
        .back-link {
            display: block;
            margin: 18px 0 0 0;
            text-align: center;
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .message {
            margin-top: 18px;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 600px) {
            .container {
                padding: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🖼️→📄 Multiple Images to PDF</h2>
    <form method="post" enctype="multipart/form-data" action="">
        <input type="file" name="image_file[]" accept="image/*" multiple required>
        <input type="submit" value="Convert to PDF">
    </form>
    <a class="back-link" href="index.php">← Back to Home</a>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file'])) {
        ob_start(); // Prevent header errors

        require_once('vendor/autoload.php');
        if (!class_exists('FPDF')) {
            require_once('fpdf.php');
        }

        if (!class_exists('FPDF')) {
            echo "<div class='message error'>FPDF library not found.</div>";
            ob_end_flush();
            exit();
        }

        $files = $_FILES['image_file'];

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        $pdf = new FPDF();

        $validImages = 0;
        $tempFiles = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$i];
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    continue; // skip invalid
                }

                $imgInfo = getimagesize($tmpName);
                if ($imgInfo === false) {
                    continue; // skip invalid
                }

                $tmpImg = sys_get_temp_dir() . '/img2pdf_' . uniqid() . '.' . $ext;
                copy($tmpName, $tmpImg);
                $tempFiles[] = $tmpImg;

                list($imgWidth, $imgHeight) = getimagesize($tmpImg);

                $pdf->AddPage();

                $pageWidth = $pdf->GetPageWidth() - 20; // 10mm margin each side
                $pageHeight = $pdf->GetPageHeight() - 20;

                $imgRatio = $imgWidth / $imgHeight;
                $pageRatio = $pageWidth / $pageHeight;

                if ($imgRatio > $pageRatio) {
                    $newWidth = $pageWidth;
                    $newHeight = $pageWidth / $imgRatio;
                } else {
                    $newHeight = $pageHeight;
                    $newWidth = $pageHeight * $imgRatio;
                }

                $x = ($pdf->GetPageWidth() - $newWidth) / 2;
                $y = ($pdf->GetPageHeight() - $newHeight) / 2;

                $pdf->Image($tmpImg, $x, $y, $newWidth, $newHeight);

                $validImages++;
            }
        }

        if ($validImages > 0) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="converted.pdf"');
            $pdf->Output('D', 'converted.pdf');
        } else {
            echo "<div class='message error'>No valid images uploaded.</div>";
        }

        // Clean up
        foreach ($tempFiles as $file) {
            unlink($file);
        }

        ob_end_flush();
        exit();
    }
    ?>
</div>
</body>
</html>
