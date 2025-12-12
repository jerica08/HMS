<?php

<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> parent of a136843 (Merge branch 'main' of https://github.com/jerica08/HMS)
/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
<<<<<<< HEAD
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

=======
 * This file is used by PHP's built-in development server to simulate
 * Apache's mod_rewrite functionality. This allows the development server
 * to properly route requests to the front controller.
 *
 * Usage:
 * php -S localhost:8080 -t public/ system/rewrite.php
 */

// If the request is for a file that exists, serve it directly
if (is_file($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_NAME'])) {
    return false;
}

// Set the environment if not already set
if (!isset($_SERVER['CI_ENVIRONMENT'])) {
    $_SERVER['CI_ENVIRONMENT'] = 'development';
}

// Route everything else to index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';

require $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'index.php';
>>>>>>> parent of a136843 (Merge branch 'main' of https://github.com/jerica08/HMS)
