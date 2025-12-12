<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * ---------------------------------------------------------------
 * PHP Built-in Server Router
 * ---------------------------------------------------------------
 *
 * This file is used by PHP's built-in development server to
 * simulate Apache's mod_rewrite functionality. It routes all
 * requests to index.php unless the file exists.
 *
 * Usage: php -S localhost:8080 -t public system/rewrite.php
 */

// Get the requested file path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requested = __DIR__ . '/../public' . $uri;

// If the request is for a real file or directory, serve it
if ($uri !== '/' && file_exists($requested) && !is_dir($requested)) {
    return false;
}

// Otherwise, route to index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/../public/index.php';

