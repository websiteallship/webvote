<?php
// Helper function để escape HTML và ngăn XSS
function escapeHtml($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>