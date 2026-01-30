<?php
// Error Recovery & Backup System

/**
 * Log error to file
 */
function logError($message, $context = [])
{
    $logDir = '../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";

    error_log($logMessage, 3, $logFile);
}

/**
 * Create backup of a file
 */
function createBackup($filepath)
{
    if (!file_exists($filepath)) {
        return false;
    }

    $backupDir = dirname($filepath) . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $filename = basename($filepath);
    $backupFile = $backupDir . '/' . pathinfo($filename, PATHINFO_FILENAME)
        . '_' . date('Y-m-d_His') . '.json';

    return copy($filepath, $backupFile);
}

/**
 * Get latest backup file
 */
function getLatestBackup($filepath)
{
    $backupDir = dirname($filepath) . '/backups';
    if (!is_dir($backupDir)) {
        return null;
    }

    $filename = pathinfo(basename($filepath), PATHINFO_FILENAME);
    $pattern = $backupDir . '/' . $filename . '_*.json';
    $backups = glob($pattern);

    if (empty($backups)) {
        return null;
    }

    // Sort by modification time, newest first
    usort($backups, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    return $backups[0];
}

/**
 * Restore from backup
 */
function restoreFromBackup($filepath)
{
    $latestBackup = getLatestBackup($filepath);
    if (!$latestBackup) {
        logError("No backup found for restoration", ['file' => $filepath]);
        return false;
    }

    $success = copy($latestBackup, $filepath);
    if ($success) {
        logError("Restored from backup", [
            'file' => $filepath,
            'backup' => $latestBackup
        ]);
    }

    return $success;
}

/**
 * Validate JSON file
 */
function validateJsonFile($filepath)
{
    if (!file_exists($filepath)) {
        return ['valid' => false, 'error' => 'File does not exist'];
    }

    $content = @file_get_contents($filepath);
    if ($content === false) {
        return ['valid' => false, 'error' => 'Failed to read file'];
    }

    if (empty($content)) {
        return ['valid' => false, 'error' => 'File is empty'];
    }

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'valid' => false,
            'error' => 'JSON parse error: ' . json_last_error_msg()
        ];
    }

    return ['valid' => true, 'data' => $data];
}

/**
 * Safe read JSON with auto-recovery
 */
function safeReadJson($filepath, $default = [])
{
    // Validate file
    $validation = validateJsonFile($filepath);

    if ($validation['valid']) {
        return $validation['data'];
    }

    // File is corrupt, log error
    logError("Corrupt JSON file detected", [
        'file' => $filepath,
        'error' => $validation['error']
    ]);

    // Try to restore from backup
    if (restoreFromBackup($filepath)) {
        // Validate restored file
        $validation = validateJsonFile($filepath);
        if ($validation['valid']) {
            logError("Successfully recovered from backup", ['file' => $filepath]);
            return $validation['data'];
        }
    }

    // No backup or backup also corrupt, create new file with default
    logError("Creating new file with default data", [
        'file' => $filepath,
        'default' => $default
    ]);

    $json = json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($filepath, $json);

    return $default;
}

/**
 * Safe write JSON with backup
 */
function safeWriteJson($filepath, $data)
{
    // Create backup before writing
    if (file_exists($filepath)) {
        createBackup($filepath);
    }

    // Write to temporary file first
    $tempFile = $filepath . '.tmp';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        logError("Failed to encode JSON", [
            'file' => $filepath,
            'error' => json_last_error_msg()
        ]);
        return false;
    }

    $bytesWritten = @file_put_contents($tempFile, $json);
    if ($bytesWritten === false) {
        logError("Failed to write temp file", ['file' => $tempFile]);
        return false;
    }

    // Validate temp file
    $validation = validateJsonFile($tempFile);
    if (!$validation['valid']) {
        logError("Temp file validation failed", [
            'file' => $tempFile,
            'error' => $validation['error']
        ]);
        @unlink($tempFile);
        return false;
    }

    // Atomic rename
    if (!@rename($tempFile, $filepath)) {
        logError("Failed to rename temp file", [
            'temp' => $tempFile,
            'target' => $filepath
        ]);
        @unlink($tempFile);
        return false;
    }

    return true;
}

/**
 * Clean old backups (keep last N backups)
 */
function cleanOldBackups($filepath, $keepCount = 10)
{
    $backupDir = dirname($filepath) . '/backups';
    if (!is_dir($backupDir)) {
        return;
    }

    $filename = pathinfo(basename($filepath), PATHINFO_FILENAME);
    $pattern = $backupDir . '/' . $filename . '_*.json';
    $backups = glob($pattern);

    if (count($backups) <= $keepCount) {
        return;
    }

    // Sort by modification time, oldest first
    usort($backups, function ($a, $b) {
        return filemtime($a) - filemtime($b);
    });

    // Delete old backups
    $toDelete = array_slice($backups, 0, count($backups) - $keepCount);
    foreach ($toDelete as $backup) {
        @unlink($backup);
    }
}
?>