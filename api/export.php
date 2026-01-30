<?php
/**
 * Export API - Export voting results to CSV or PDF
 * Requires admin authentication
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Authentication check
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get export format
$format = $_GET['format'] ?? 'csv';
if (!in_array($format, ['csv', 'pdf'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid format']);
    exit;
}

// Load data files
$dataDir = __DIR__ . '/../data/';
$sessionFile = $dataDir . 'session.json';
$performersFile = $dataDir . 'performers.json';
$votesFile = $dataDir . 'votes.json';

// Read session data
$session = json_decode(file_get_contents($sessionFile), true);
if (!$session) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load session']);
    exit;
}

// Read performers
$performers = json_decode(file_get_contents($performersFile), true);
if (!$performers) {
    $performers = [];
}

// Read votes
$votes = json_decode(file_get_contents($votesFile), true);
if (!$votes) {
    $votes = [];
}

// Calculate results
$results = calculateResults($performers, $votes);

// Export based on format
if ($format === 'csv') {
    exportCSV($results, $session);
} else {
    exportPDF($results, $session);
}

/**
 * Calculate voting results with rankings
 */
function calculateResults($performers, $votes)
{
    $scores = [];

    // Initialize scores
    foreach ($performers as $performer) {
        $scores[$performer['id']] = [
            'id' => $performer['id'],
            'name' => $performer['name'],
            'total_score' => 0,
            'rank1' => 0,
            'rank2' => 0,
            'rank3' => 0,
            'total_votes' => 0
        ];
    }

    // Calculate scores (same logic as results.php)
    foreach ($votes as $vote) {
        if (isset($vote['votes']) && is_array($vote['votes'])) {
            // Process rank1 (3 points)
            if (isset($vote['votes']['rank1'])) {
                $performerId = $vote['votes']['rank1'];
                if (isset($scores[$performerId])) {
                    $scores[$performerId]['total_score'] += 3;
                    $scores[$performerId]['rank1']++;
                    $scores[$performerId]['total_votes']++;
                }
            }

            // Process rank2 (2 points)
            if (isset($vote['votes']['rank2'])) {
                $performerId = $vote['votes']['rank2'];
                if (isset($scores[$performerId])) {
                    $scores[$performerId]['total_score'] += 2;
                    $scores[$performerId]['rank2']++;
                    // Don't increment total_votes again if already counted in rank1
                    if (!isset($vote['votes']['rank1']) || $vote['votes']['rank1'] != $performerId) {
                        $scores[$performerId]['total_votes']++;
                    }
                }
            }

            // Process rank3 (1 point)
            if (isset($vote['votes']['rank3'])) {
                $performerId = $vote['votes']['rank3'];
                if (isset($scores[$performerId])) {
                    $scores[$performerId]['total_score'] += 1;
                    $scores[$performerId]['rank3']++;
                    // Don't increment total_votes again if already counted
                    if (
                        (!isset($vote['votes']['rank1']) || $vote['votes']['rank1'] != $performerId) &&
                        (!isset($vote['votes']['rank2']) || $vote['votes']['rank2'] != $performerId)
                    ) {
                        $scores[$performerId]['total_votes']++;
                    }
                }
            }
        }
    }

    // Sort by total score descending
    usort($scores, function ($a, $b) {
        if ($b['total_score'] !== $a['total_score']) {
            return $b['total_score'] - $a['total_score'];
        }
        return $b['rank1'] - $a['rank1'];
    });

    return $scores;
}

/**
 * Export results as CSV
 */
function exportCSV($results, $session)
{
    $filename = 'results_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Session info header
    fputcsv($output, ['KẾT QUẢ BÌNH CHỌN ALLSHIP GALA DINNER']);
    fputcsv($output, ['Phiên bình chọn:', $session['start_time'] ?? 'N/A']);
    fputcsv($output, ['Thời gian:', ($session['duration'] ?? 0) . ' phút']);
    fputcsv($output, ['Tổng số phiếu:', count(json_decode(file_get_contents(__DIR__ . '/../data/votes.json'), true))]);
    fputcsv($output, []); // Empty row

    // Column headers
    fputcsv($output, ['Hạng', 'Tiết mục', 'Điểm tổng', 'Hạng 1', 'Hạng 2', 'Hạng 3', 'Tổng phiếu']);

    // Data rows
    $rank = 1;
    foreach ($results as $result) {
        fputcsv($output, [
            $rank++,
            $result['name'],
            $result['total_score'],
            $result['rank1'],
            $result['rank2'],
            $result['rank3'],
            $result['total_votes']
        ]);
    }

    fclose($output);
    exit;
}

/**
 * Export results as PDF
 */
function exportPDF($results, $session)
{
    require_once(__DIR__ . '/../lib/fpdf/fpdf.php');

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Add Vietnamese font support (using Arial Unicode MS or similar)
    // For now, we'll use standard fonts and transliterate if needed
    $pdf->SetFont('Arial', 'B', 16);

    // Header
    $pdf->Cell(0, 10, 'KET QUA BINH CHON ALLSHIP GALA DINNER', 0, 1, 'C');
    $pdf->Ln(5);

    // Session info
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Phien: ' . ($session['start_time'] ?? 'N/A'), 0, 1);
    $pdf->Cell(0, 6, 'Thoi gian: ' . ($session['duration'] ?? 0) . ' phut', 0, 1);
    $pdf->Cell(0, 6, 'Tong so phieu: ' . count(json_decode(file_get_contents(__DIR__ . '/../data/votes.json'), true)), 0, 1);
    $pdf->Ln(10);

    // Top 3 Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 8, 'TOP 3 TIET MUC XUAT SAC', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 12);
    $medals = ['1st', '2nd', '3rd'];
    for ($i = 0; $i < min(3, count($results)); $i++) {
        $result = $results[$i];
        $pdf->Cell(0, 8, $medals[$i] . ': ' . transliterate($result['name']) . ' - ' . $result['total_score'] . ' diem', 0, 1, 'C');
    }
    $pdf->Ln(10);

    // Full results table
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'BANG XEP HANG CHI TIET', 0, 1, 'C');
    $pdf->Ln(3);

    // Table header
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(15, 7, 'Hang', 1, 0, 'C');
    $pdf->Cell(60, 7, 'Tiet muc', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Diem', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Hang 1', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Hang 2', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Hang 3', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Tong phieu', 1, 1, 'C');

    // Table rows
    $pdf->SetFont('Arial', '', 9);
    $rank = 1;
    foreach ($results as $result) {
        $pdf->Cell(15, 6, $rank++, 1, 0, 'C');
        $pdf->Cell(60, 6, transliterate($result['name']), 1, 0, 'L');
        $pdf->Cell(25, 6, $result['total_score'], 1, 0, 'C');
        $pdf->Cell(20, 6, $result['rank1'], 1, 0, 'C');
        $pdf->Cell(20, 6, $result['rank2'], 1, 0, 'C');
        $pdf->Cell(20, 6, $result['rank3'], 1, 0, 'C');
        $pdf->Cell(30, 6, $result['total_votes'], 1, 1, 'C');
    }

    // Footer
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'Xuat luc: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

    // Output PDF
    $filename = 'results_' . date('Ymd_His') . '.pdf';
    $pdf->Output('D', $filename);
    exit;
}

/**
 * Simple transliteration for Vietnamese characters
 * (Basic implementation - can be improved with full Vietnamese character map)
 */
function transliterate($text)
{
    $vietnamese = [
        'à',
        'á',
        'ạ',
        'ả',
        'ã',
        'â',
        'ầ',
        'ấ',
        'ậ',
        'ẩ',
        'ẫ',
        'ă',
        'ằ',
        'ắ',
        'ặ',
        'ẳ',
        'ẵ',
        'è',
        'é',
        'ẹ',
        'ẻ',
        'ẽ',
        'ê',
        'ề',
        'ế',
        'ệ',
        'ể',
        'ễ',
        'ì',
        'í',
        'ị',
        'ỉ',
        'ĩ',
        'ò',
        'ó',
        'ọ',
        'ỏ',
        'õ',
        'ô',
        'ồ',
        'ố',
        'ộ',
        'ổ',
        'ỗ',
        'ơ',
        'ờ',
        'ớ',
        'ợ',
        'ở',
        'ỡ',
        'ù',
        'ú',
        'ụ',
        'ủ',
        'ũ',
        'ư',
        'ừ',
        'ứ',
        'ự',
        'ử',
        'ữ',
        'ỳ',
        'ý',
        'ỵ',
        'ỷ',
        'ỹ',
        'đ',
        'À',
        'Á',
        'Ạ',
        'Ả',
        'Ã',
        'Â',
        'Ầ',
        'Ấ',
        'Ậ',
        'Ẩ',
        'Ẫ',
        'Ă',
        'Ằ',
        'Ắ',
        'Ặ',
        'Ẳ',
        'Ẵ',
        'È',
        'É',
        'Ẹ',
        'Ẻ',
        'Ẽ',
        'Ê',
        'Ề',
        'Ế',
        'Ệ',
        'Ể',
        'Ễ',
        'Ì',
        'Í',
        'Ị',
        'Ỉ',
        'Ĩ',
        'Ò',
        'Ó',
        'Ọ',
        'Ỏ',
        'Õ',
        'Ô',
        'Ồ',
        'Ố',
        'Ộ',
        'Ổ',
        'Ỗ',
        'Ơ',
        'Ờ',
        'Ớ',
        'Ợ',
        'Ở',
        'Ỡ',
        'Ù',
        'Ú',
        'Ụ',
        'Ủ',
        'Ũ',
        'Ư',
        'Ừ',
        'Ứ',
        'Ự',
        'Ử',
        'Ữ',
        'Ỳ',
        'Ý',
        'Ỵ',
        'Ỷ',
        'Ỹ',
        'Đ'
    ];

    $latin = [
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'a',
        'e',
        'e',
        'e',
        'e',
        'e',
        'e',
        'e',
        'e',
        'e',
        'e',
        'e',
        'i',
        'i',
        'i',
        'i',
        'i',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'o',
        'u',
        'u',
        'u',
        'u',
        'u',
        'u',
        'u',
        'u',
        'u',
        'u',
        'u',
        'y',
        'y',
        'y',
        'y',
        'y',
        'd',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'A',
        'E',
        'E',
        'E',
        'E',
        'E',
        'E',
        'E',
        'E',
        'E',
        'E',
        'E',
        'I',
        'I',
        'I',
        'I',
        'I',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'O',
        'U',
        'U',
        'U',
        'U',
        'U',
        'U',
        'U',
        'U',
        'U',
        'U',
        'U',
        'Y',
        'Y',
        'Y',
        'Y',
        'Y',
        'D'
    ];

    return str_replace($vietnamese, $latin, $text);
}
