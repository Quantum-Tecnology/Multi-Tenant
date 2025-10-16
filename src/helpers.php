<?php

declare(strict_types=1);

if (! function_exists('tenant')) {
    function tenant()
    {
        return app('tenant');
    }
}
