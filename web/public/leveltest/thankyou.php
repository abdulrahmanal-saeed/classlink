<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /level-test/thank-you' . ($query ? '?' . $query : ''), true, 301);
exit;
