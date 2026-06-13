<?php
/**
 * ELITE-4 Nepal - API: Stats
 */
header('Content-Type: application/json');
require_once 'config.php';

echo json_encode(getStats());