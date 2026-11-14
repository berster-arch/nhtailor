<?php
if (!defined('NH_ADMIN_AUTHORIZED')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan. Silakan buka melalui dashboard.');
}
