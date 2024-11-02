<?php

require_once('../tools/functions.php');
require_once('../classes/product.class.php');

$code = $name = $category = $price = $image = '';
$codeErr = $nameErr = $categoryErr = $priceErr = $imageErr = '';

$productObj = new Product();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $code = clean_input($_POST['code']);
    $name = clean_input($_POST['name']);
    $category = clean_input($_POST['category']);
    $price = clean_input($_POST['price']);
    
    // Handle file upload
    if (isset($_FILES['image'])) {
        $image = $_FILES['image'];
        // Check if there was an error uploading the file
        if ($image['error'] !== UPLOAD_ERR_OK) {
            $imageErr = 'An error occurred during file upload.';
        } else {
            // Check the file size (5MB = 5 * 1024 * 1024 bytes)
            if ($image['size'] > 5 * 1024 * 1024) {
                $imageErr = 'File size must not exceed 5MB.';
            }
            // You may also want to check the file type here (e.g., image/jpeg, image/png)
            $allowedTypes = ['image/jpeg', 'image/png'];
            if (!in_array($image['type'], $allowedTypes)) {
                $imageErr = 'Invalid file type. Only JPG and PNG files are allowed.';
            }
        }
    }

    if (empty($code)) {
        $codeErr = 'Product Code is required.';
    } else if ($productObj->codeExists($code)) {
        $codeErr = 'Product Code already exists.';
    }

    if (empty($name)) {
        $nameErr = 'Name is required.';
    }

    if (empty($category)) {
        $categoryErr = 'Category is required.';
    }

    if (empty($price)) {
        $priceErr = 'Price is required.';
    } else if (!is_numeric($price)) {
        $priceErr = 'Price should be a number.';
    } else if ($price < 1) {
        $priceErr = 'Price must be greater than 0.';
    }

    // If there are validation errors, return them as JSON
    if (!empty($codeErr) || !empty($nameErr) || !empty($categoryErr) || !empty($priceErr) || !empty($imageErr)) {
        echo json_encode([
            'status' => 'error',
            'codeErr' => $codeErr,
            'nameErr' => $nameErr,
            'categoryErr' => $categoryErr,
            'priceErr' => $priceErr,
            'imageErr' => $imageErr
        ]);
        exit;
    }

    if (empty($codeErr) && empty($nameErr) && empty($categoryErr) && empty($priceErr) && empty($imageErr)) {
        $productObj->code = $code;
        $productObj->name = $name;
        $productObj->category_id = $category;
        $productObj->price = $price;

        // Process file upload
        $targetDir = "../uploads/";
        $targetFile = $targetDir . basename($image['name']);
        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            $productObj->image = $targetFile; // Save the path of the uploaded image
            if ($productObj->add()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Something went wrong when adding the new product.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        }
        exit;
    }
}
?>
