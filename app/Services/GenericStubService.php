<?php

namespace App\Services;

/**
 * Catch-all stub for any service that uses the executeService(action, params) pattern
 * but has no real backend implementation yet.
 *
 * Returns safe empty responses so the client doesn't crash on unknown calls.
 */
class GenericStubService
{
    public function executeService($action, $params = []): array
    {
        return ['status' => 1];
    }
}
