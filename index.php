<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Converter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .converter-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }

        .converter-card {
            display: flex;
            align-items: center;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            padding: 20px 24px;
            text-decoration: none;
            color: #222;
            transition: box-shadow 0.2s, transform 0.2s, background 0.2s;
            border: 1px solid #e0e0e0;
            position: relative;
        }

        .converter-card:hover {
            background: #eaf4ff;
            box-shadow: 0 4px 16px rgba(0, 123, 255, 0.1);
            transform: translateY(-2px) scale(1.02);
            border-color: #90caf9;
        }

        .converter-card .icon {
            font-size: 2.2rem;
            margin-right: 22px;
            flex-shrink: 0;
        }

        .converter-card .title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-right: 18px;
            min-width: 140px;
        }

        .converter-card .desc {
            color: #666;
            font-size: 1rem;
        }

        @media (max-width: 600px) {
            .container {
                padding: 12px;
            }

            .converter-card {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px;
            }

            .converter-card .icon {
                margin-bottom: 8px;
                margin-right: 0;
            }

            .converter-card .title {
                margin-right: 0;
                margin-bottom: 4px;
            }
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>File Converter</h2>
        <div class="converter-list">
            <a class="converter-card" href="image_to_pdf.php">
                <span class="icon">🖼️→📄</span>
                <span class="title">Image → PDF</span>
                <span class="desc">Convert images (JPG, PNG, GIF) to PDF</span>
            </a>
            <a class="converter-card" href="pdf_to_image.php">
                <span class="icon">📄→🖼️</span>
                <span class="title">PDF → JPG/PNG</span>
                <span class="desc">Convert PDF pages to images</span>
            </a>
            <a class="converter-card" href="excel_to_csv.php">
                <span class="icon">📊→📄</span>
                <span class="title">Excel → CSV</span>
                <span class="desc">Convert Excel files to CSV format</span>
            </a>
            <a class="converter-card" href="archive_extractor.php">
                <span class="icon">🗜️</span>
                <span class="title">ZIP/RAR Extractor</span>
                <span class="desc">Extract ZIP or RAR archives</span>
            </a>
        </div>
        <?php
        if (isset($_GET['message'])) {
            $message = htmlspecialchars($_GET['message']);
            $type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';
            echo "<div class='message $type'>$message</div>";
        }
        ?>
    </div>
</body>

</html>