<?php
require_once __DIR__ . '/../includes/legal-content.php';
include __DIR__ . '/../includes/header.php';

$doc_title    = 'Privacy Policy';
$doc_subtitle = 'How Findownn collects, uses, and protects your information.';
$doc_kind     = 'privacy';
$sections     = legal_privacy_sections();

include __DIR__ . '/../includes/partials/legal-document.php';
include __DIR__ . '/../includes/footer.php';
