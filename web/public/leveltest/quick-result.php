<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /level-test/quick-result' . ($query ? '?' . $query : ''), true, 301);
exit;
