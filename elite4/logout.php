<?php
/**
 * ELITE-4 Nepal - Logout Handler
 */
session_start();
session_destroy();
header("Location: index.php");
exit;