<?php

declare(strict_types=1);

it('pins database queue connection to central', function (): void {
    expect(config('queue.connections.database.connection'))
        ->toBe('central');
});

it('pins batching to central database', function (): void {
    $batching = config('queue.batching');

    expect($batching['database'] ?? null)->toBe('central')
        ->and($batching['table'] ?? null)->toBe('job_batches');
});

it('pins failed jobs to central database', function (): void {
    $failed = config('queue.failed');

    expect($failed['database'] ?? null)->toBe('central')
        ->and($failed['table'] ?? null)->toBe('failed_jobs');
});
