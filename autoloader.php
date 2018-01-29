<?php

return function ($prefix, $baseDir) {
    spl_autoload_register(function ($class) use ($prefix, $baseDir)
    {
        // Check if this class uses a namespace
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0)
        {
            return;
        }

        $relativeClass = substr($class, $len);

        // Get the file name using the namespace
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        // We've got the file
        if (file_exists($file))
        {
            require $file;
        }
    });
};