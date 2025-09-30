<?php
// Script to fix all single quotes in upload.php

$content = file_get_contents('upload.php');

// Find where PHP ends (before the HTML/style section)
$phpEndPos = strpos($content, 'include __DIR__');
if ($phpEndPos === false) {
    die("Could not find PHP/HTML boundary\n");
}

// Split into PHP and HTML parts  
$phpPart = substr($content, 0, $phpEndPos + 100); // Get PHP part plus the include line
$htmlPart = substr($content, $phpEndPos + 100);

// Replace all single-quoted array keys with double quotes
$phpPart = preg_replace('/\[\'([^\']+)\'\]/', '["$1"]', $phpPart);

// Replace all single-quoted strings with double quotes
// This regex looks for single quotes that are preceded by =, (, [, {, comma, or space
$phpPart = preg_replace('/([\s=\(\[\{,>])\'([^\']*?)\'/', '$1"$2"', $phpPart);

// Fix any remaining standalone single-quoted strings
$phpPart = str_replace("'status'", '"status"', $phpPart);
$phpPart = str_replace("'error'", '"error"', $phpPart);  
$phpPart = str_replace("'message'", '"message"', $phpPart);
$phpPart = str_replace("'success'", '"success"', $phpPart);

// Put it back together
$fixedContent = $phpPart . $htmlPart;

// Write the fixed file
file_put_contents('upload.php', $fixedContent);

echo "Fixed all quotes in upload.php!\n";
echo "Single quotes remaining in PHP section: " . substr_count($phpPart, "'") . "\n";
