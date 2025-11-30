<?php
/**
 * File Upload Utility Functions
 * Helper functions for handling file uploads
 */

/**
 * Validate image upload
 * 
 * @param array $file $_FILES array element
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_image_upload($file)
{
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload error: ' . $file['error']];
    }
    
    // Validate file size (5MB max)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File size exceeds 5MB limit'];
    }
    
    // Validate MIME type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'error' => 'Invalid file type. Only JPG, PNG, and WEBP images are allowed'];
    }
    
    // Validate file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['valid' => false, 'error' => 'Invalid file extension'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Generate unique filename
 * 
 * @param string $prefix Filename prefix (e.g., 'completion', 'profile')
 * @param int $id Related ID (e.g., booking_id, user_id)
 * @param string $extension File extension
 * @return string Unique filename
 */
function generate_unique_filename($prefix, $id, $extension)
{
    $timestamp = time();
    $random = substr(md5(uniqid(rand(), true)), 0, 8);
    return "{$prefix}_{$id}_{$timestamp}_{$random}.{$extension}";
}

/**
 * Save uploaded file
 * 
 * @param array $file $_FILES array element
 * @param string $destination_path Full path where file should be saved
 * @return array ['success' => bool, 'error' => string|null, 'path' => string|null]
 */
function save_uploaded_file($file, $destination_path)
{
    // Validate the upload first
    $validation = validate_image_upload($file);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error'], 'path' => null];
    }
    
    // Ensure directory exists
    $directory = dirname($destination_path);
    if (!file_exists($directory)) {
        if (!mkdir($directory, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory', 'path' => null];
        }
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination_path)) {
        // Set proper permissions
        chmod($destination_path, 0644);
        return ['success' => true, 'error' => null, 'path' => $destination_path];
    }
    
    return ['success' => false, 'error' => 'Failed to save file', 'path' => null];
}

/**
 * Delete uploaded file
 * 
 * @param string $file_path Path to file to delete
 * @return bool True if deleted, false otherwise
 */
function delete_uploaded_file($file_path)
{
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * Get file size in human-readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function format_file_size($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
