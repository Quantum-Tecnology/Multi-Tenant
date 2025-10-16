<?php

declare(strict_types=1);

if (! function_exists('tenant')) {
    function tenant()
    {
        return app('tenant');
    }
}

if (! function_exists('tenantLogAndPrint')) {

    function tenantLogAndPrint(string $message, string $level = 'info', bool $console = false): void
    {
        logger()->{$level}($message);

        if (app()->runningInConsole() && $console) {
            $color = match ($level) {
                'error' => "\033[31m", // red
                'warning' => "\033[33m", // yellow
                'info' => "\033[32m", // green
                default => "\033[0m",
            };

            echo "{$color}{$message}\033[0m".PHP_EOL;
        }
    }
}
