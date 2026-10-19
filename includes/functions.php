<?php
// Small helper functions used by many pages

// Make text safe to print inside HTML
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Format money: 14000 -> 14,000
function money($n) {
    return number_format((float)$n, 0);
}

// Return a coloured badge for stock status
function stock_badge($qty, $reorder) {
    if ($qty <= 0)         return '<span class="badge badge-danger">Out</span>';
    if ($qty <= $reorder)  return '<span class="badge badge-warning">Low</span>';
    return '<span class="badge badge-success">OK</span>';
}
