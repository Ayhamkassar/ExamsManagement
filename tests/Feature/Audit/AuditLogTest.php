<?php

use App\Models\AuditLog;
use App\Services\Audit\AuditLogger;

it('creates immutable audit records', function () {
    $logger = app(AuditLogger::class);

    $log = $logger->log('test.action', metadata: ['source' => 'test']);

    expect($log)->toBeInstanceOf(AuditLog::class)
        ->and($log->action)->toBe('test.action');

    expect(fn () => $log->update(['action' => 'changed']))
        ->toThrow(RuntimeException::class);

    expect(fn () => $log->delete())
        ->toThrow(RuntimeException::class);
});
