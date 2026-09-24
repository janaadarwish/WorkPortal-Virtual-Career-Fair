<?php
session_start();

// Security: Check if recruiter is logged in
if (!isset($_SESSION['User_ID'])) {
    die("Unauthorized access.");
}

$zipName = 'Bulk_Resumes_' . date('Y-m-d') . '.zip';
$zip = new ZipArchive;

// Path adjustment based on your image: 
// From Models folder, go up one level to root, then into uploads/resumes
$uploadDir = '../uploads/resumes/'; 

if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_file($uploadDir . $file)) {
                // Adds file to zip; the second parameter removes the folder path from inside the zip
                $zip->addFile($uploadDir . $file, $file);
            }
        }
    }
    $zip->close();

    // Headers to force download
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=' . $zipName);
    header('Content-Length: ' . filesize($zipName));
    
    // Clean up output buffer to prevent corrupt zip files
    ob_clean();
    flush();
    
    readfile($zipName);

    // Delete temporary zip from server after download
    unlink($zipName);
    exit;
} else {
    die("Error: Could not create zip file.");
}