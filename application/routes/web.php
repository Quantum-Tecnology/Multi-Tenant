<?php

declare(strict_types=1);

use App\Brain\Customer\Processes\CreateCustomer;
use App\Jobs\Test\BatchJob;
use App\Jobs\Test\SimpleJob;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): View => view('welcome'));

Route::get('/job-1', function (): string {
    Customer::create(['name' => 'Customer 01: '.now()->toDateTimeString()]);

    SimpleJob::dispatch();

    Customer::create(['name' => 'Customer 02: '.now()->toDateTimeString()]);

    return 'oi';
});

Route::get('/brain', function (): string {
    CreateCustomer::dispatch();

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
