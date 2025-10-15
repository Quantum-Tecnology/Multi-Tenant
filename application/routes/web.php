<?php

declare(strict_types=1);

use App\Jobs\Test\BatchJob;
use App\Jobs\Test\SimpleJob;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): View => view('welcome'));

Route::get('/job-1', function (): string {
    SimpleJob::dispatch();

    return 'oi';
});

Route::get('/job-2', function (): string {
    Illuminate\Support\Facades\Bus::batch([
        new BatchJob(),
    ])->dispatch();

    return 'oi';
});

Route::get('/cache', function (): string {
    Illuminate\Support\Facades\Cache::set('test', 'oi');

    return 'oi';
});
