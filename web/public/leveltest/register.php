<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /level-test/register' . ($query ? '?' . $query : ''), true, 301);
exit;
