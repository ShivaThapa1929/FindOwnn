<?php
require_once __DIR__ . '/../includes/legal-content.php';
include __DIR__ . '/../includes/header.php';

$doc_title    = 'Terms & Conditions';
$doc_subtitle = 'Rules for using Findownn as a player or venue partner.';
$doc_kind     = 'terms';
$sections     = legal_terms_sections();

include __DIR__ . '/../includes/partials/legal-document.php';
include __DIR__ . '/../includes/footer.php';
