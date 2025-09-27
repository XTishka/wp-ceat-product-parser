<?php
declare(strict_types=1);

/**
 * Simple PSR-4 style autoloader for the Ceat Product Parser plugin.
 */

spl_autoload_register(
    static function (string $class): void {
        $prefix   = 'CeatProductParser\\';
        $base_dir = CEAT_PP_PLUGIN_DIR . 'src/';
        $len      = strlen($prefix);

        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $relative_path  = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);
        $file           = $base_dir . $relative_path . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
);
