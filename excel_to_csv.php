<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Excel to CSV Converter</title>
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
    <h2>📊→📄 Excel to CSV</h2>
    <form method="post" enctype="multipart/form-data" action="">
        <input type="file" name="excel_file" accept=".xls,.xlsx" required>
        <input type="submit" value="Convert to CSV">
    </form>
    <a class="back-link" href="index.php">← Back to Home</a>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
        $file = $_FILES['excel_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo "<div class='message error'>File upload error.</div>";
        } else {
            require_once('vendor/autoload.php');
            if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                echo "<div class='message error'>PhpSpreadsheet library not found. Please install it via Composer.</div>";
            } else {
                $tmpName = $file['tmp_name'];
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpName);
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $csvFile = sys_get_temp_dir() . '/excel2csv_' . uniqid() . '.csv';
                $writer->save($csvFile);
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="converted.csv"');
                header('Content-Length: ' . filesize($csvFile));
                readfile($csvFile);
                unlink($csvFile);
                exit();
            }
        }
    }
    ?>
</div>
</body>
</html>
