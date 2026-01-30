<?php
// File Locking Helper - Ngăn chặn race conditions
require_once __DIR__ . '/error_recovery.php';

/**
 * Đọc file JSON với file locking và error recovery
 * @param string $filepath Đường dẫn file
 * @param mixed $default Giá trị mặc định nếu file không tồn tại
 * @return mixed Dữ liệu đã decode
 */
function readJsonWithLock($filepath, $default = [])
{
    // Use safe read with auto-recovery
    return safeReadJson($filepath, $default);
}

/**
 * Ghi file JSON với file locking, backup và validation
 * @param string $filepath Đường dẫn file
 * @param mixed $data Dữ liệu cần ghi
 * @return bool Thành công hay không
 */
function writeJsonWithLock($filepath, $data)
{
    // Use safe write with backup and validation
    $success = safeWriteJson($filepath, $data);

    // Clean old backups (keep last 10)
    if ($success) {
        cleanOldBackups($filepath, 10);
    }

    return $success;
}

/**
 * Atomic update - Đọc, modify, ghi trong 1 transaction
 * @param string $filepath Đường dẫn file
 * @param callable $callback Function nhận data cũ, return data mới
 * @param mixed $default Giá trị mặc định
 * @return bool Thành công hay không
 */
function atomicJsonUpdate($filepath, $callback, $default = [])
{
    // Tạo file nếu chưa tồn tại
    if (!file_exists($filepath)) {
        file_put_contents($filepath, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $fp = fopen($filepath, 'c+');
    if (!$fp) {
        return false;
    }

    // Exclusive lock ngay từ đầu
    if (flock($fp, LOCK_EX)) {
        // Đọc data hiện tại
        $content = '';
        $size = filesize($filepath);
        if ($size > 0) {
            $content = fread($fp, $size);
        }

        $currentData = json_decode($content, true) ?: $default;

        // Gọi callback để modify data
        $newData = $callback($currentData);

        // Ghi data mới
        ftruncate($fp, 0);
        rewind($fp);
        $json = json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        fwrite($fp, $json);
        fflush($fp);

        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    fclose($fp);
    return false;
}
?>