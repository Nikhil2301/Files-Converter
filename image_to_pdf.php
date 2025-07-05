<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
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
      max-width: 640px;
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
    input[type="file"],
    select {
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
    .preview {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }
    .preview-item {
      position: relative;
      width: 100px;
      height: 100px;
      overflow: hidden;
      border: 2px solid #ddd;
      border-radius: 6px;
    }
    .preview-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .preview-item button {
      position: absolute;
      top: 2px;
      right: 2px;
      background: rgba(0,0,0,0.5);
      color: white;
      border: none;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      cursor: pointer;
      font-size: 14px;
      line-height: 24px;
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
  <h2>🖼️→📄 Images to PDF (with Layout)</h2>
  <form id="uploadForm" method="post" enctype="multipart/form-data" action="">
    <label>Select Images:</label>
    <input type="file" id="imageInput" name="image_file[]" accept="image/*" multiple required>

    <label for="layout">Layout Style:</label>
    <select name="layout" id="layout" required>
      <option value="single">One image per page</option>
      <option value="two">Two images side by side</option>
      <option value="grid">Four images (2x2 grid)</option>
    </select>

    <div id="preview" class="preview"></div>

    <input type="submit" value="Convert to PDF">
  </form>
  <a class="back-link" href="index.php">← Back to Home</a>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file'])) {
    ob_start();

    require_once('vendor/autoload.php');
    if (!class_exists('FPDF')) {
      require_once('fpdf.php');
    }

    if (!class_exists('FPDF')) {
      echo "<div class='message error'>FPDF library not found.</div>";
      ob_end_flush();
      exit();
    }

    $layout = isset($_POST['layout']) ? $_POST['layout'] : 'single';
    $imagesPerPage = 1;
    if ($layout === 'two') {
      $imagesPerPage = 2;
    } elseif ($layout === 'grid') {
      $imagesPerPage = 4;
    }

    $files = $_FILES['image_file'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    $pdf = new FPDF();
    $validImages = 0;
    $tempFiles = [];
    $pageImages = [];

    for ($i = 0; $i < count($files['name']); $i++) {
      if ($files['error'][$i] === UPLOAD_ERR_OK) {
        $tmpName = $files['tmp_name'][$i];
        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) continue;

        $imgInfo = getimagesize($tmpName);
        if ($imgInfo === false) continue;

        $tmpImg = sys_get_temp_dir() . '/img2pdf_' . uniqid() . '.' . $ext;
        copy($tmpName, $tmpImg);
        $tempFiles[] = $tmpImg;

        $pageImages[] = $tmpImg;
        $validImages++;

        if (count($pageImages) === $imagesPerPage) {
          $pdf->AddPage();
          addImagesToPage($pdf, $pageImages, $layout);
          $pageImages = [];
        }
      }
    }

    if (count($pageImages) > 0) {
      $pdf->AddPage();
      addImagesToPage($pdf, $pageImages, $layout);
    }

    if ($validImages > 0) {
      header('Content-Type: application/pdf');
      header('Content-Disposition: attachment; filename="converted.pdf"');
      $pdf->Output('D', 'converted.pdf');
    } else {
      echo "<div class='message error'>No valid images uploaded.</div>";
    }

    foreach ($tempFiles as $file) {
      unlink($file);
    }

    ob_end_flush();
    exit();
  }

  function addImagesToPage($pdf, $images, $layout) {
    if ($layout === 'single') {
      $img = $images[0];
      list($w, $h) = getimagesize($img);
      $pageW = $pdf->GetPageWidth() - 20;
      $pageH = $pdf->GetPageHeight() - 20;
      $imgRatio = $w / $h;
      $pageRatio = $pageW / $pageH;

      if ($imgRatio > $pageRatio) {
        $newW = $pageW;
        $newH = $pageW / $imgRatio;
      } else {
        $newH = $pageH;
        $newW = $pageH * $imgRatio;
      }

      $x = ($pdf->GetPageWidth() - $newW) / 2;
      $y = ($pdf->GetPageHeight() - $newH) / 2;

      $pdf->Image($img, $x, $y, $newW, $newH);

    } elseif ($layout === 'two') {
      $pageW = $pdf->GetPageWidth() - 20;
      $pageH = $pdf->GetPageHeight() - 20;

      $imgW = $pageW / 2 - 5;
      $x1 = 10;
      $x2 = 10 + $imgW + 5;
      $y = 30;

      for ($i = 0; $i < 2; $i++) {
        if (!isset($images[$i])) continue;
        list($w, $h) = getimagesize($images[$i]);
        $ratio = $w / $h;
        $newH = $imgW / $ratio;
        if ($newH > $pageH) $newH = $pageH;
        $x = $i === 0 ? $x1 : $x2;
        $pdf->Image($images[$i], $x, $y, $imgW, 0);
      }

    } elseif ($layout === 'grid') {
      $pageW = $pdf->GetPageWidth() - 20;
      $pageH = $pdf->GetPageHeight() - 20;

      $cellW = $pageW / 2 - 5;
      $cellH = $pageH / 2 - 5;

      for ($i = 0; $i < 4; $i++) {
        if (!isset($images[$i])) continue;
        $col = $i % 2;
        $row = floor($i / 2);

        $x = 10 + $col * ($cellW + 5);
        $y = 20 + $row * ($cellH + 5);

        list($w, $h) = getimagesize($images[$i]);
        $ratio = $w / $h;

        $newW = $cellW;
        $newH = $cellW / $ratio;

        if ($newH > $cellH) {
          $newH = $cellH;
          $newW = $cellH * $ratio;
        }

        $xOffset = ($cellW - $newW) / 2;
        $yOffset = ($cellH - $newH) / 2;

        $pdf->Image($images[$i], $x + $xOffset, $y + $yOffset, $newW, $newH);
      }
    }
  }
  ?>
</div>

<script>
  const imageInput = document.getElementById('imageInput');
  const preview = document.getElementById('preview');

  let filesArray = [];

  imageInput.addEventListener('change', (event) => {
    const files = Array.from(event.target.files);
    filesArray = files; 
    renderPreviews();
  });

  function renderPreviews() {
    preview.innerHTML = '';
    filesArray.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        const div = document.createElement('div');
        div.className = 'preview-item';
        div.innerHTML = `
          <img src="${e.target.result}" alt="Image Preview">
          <button onclick="removeImage(${index})">&times;</button>
        `;
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });

    updateInput();
  }

  function removeImage(index) {
    filesArray.splice(index, 1);
    renderPreviews();
  }

  function updateInput() {
    const dataTransfer = new DataTransfer();
    filesArray.forEach(file => dataTransfer.items.add(file));
    imageInput.files = dataTransfer.files;
  }
</script>
</body>
</html>
