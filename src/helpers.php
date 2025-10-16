<?php

declare(strict_types=1);

if (! function_exists('tenant')) {
    function tenant()
    {
        return app('tenant');
    }
}

if (! function_exists('tenantLogAndPrint')) {

    function tenantLogAndPrint(string $message, string $level = 'info'): void
    {
        logger()->{$level}($message);

        if (app()->runningInConsole()) {
            $color = match ($level) {
                'error' => "\033[31m", // vermelho
                'warning' => "\033[33m", // amarelo
                'info' => "\033[32m", // verde
                default => "\033[0m",
            };

            echo "{$color}{$message}\033[0m".PHP_EOL;
        }
    }
}
