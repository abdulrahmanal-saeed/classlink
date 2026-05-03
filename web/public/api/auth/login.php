<?php
/**
 * Public web wrapper for POST /api/auth/login.
 *
 * The real logic lives in backend/php/api/auth/login.php so the code stays
 * organized by backend responsibility while the public route remains simple.
 */

require_once __DIR__ . '/../../../../backend/php/api/auth/login.php';
