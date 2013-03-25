<?php

$main = function () {
    $basePath = realpath(__DIR__ . '/../');
    $file = "{$basePath}/vendor/autoload.php";
    if (!file_exists($file)) {
        system("cd {$basePath}; composer install");
    }
};

$main();
unset($main);

