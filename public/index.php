<?php

declare(strict_types=1);

$pathsPath = __DIR__ . '/../app/Config/Paths.php';

if (! is_file($pathsPath)) {
    http_response_code(503);
    exit('Paths configuration not found.');
}

require $pathsPath;

$paths = new Config\Paths();

require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));
