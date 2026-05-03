<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /level-test/start' . ($query ? '?' . $query : ''), true, 301);
exit;
