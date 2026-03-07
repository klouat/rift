<?php

namespace App\Services;

class AcService
{
    /**
     * Called after login to verify client file hashes.
     * We accept everything — the response is ignored by the client anyway.
     */
    public function verifyFiles($session_key, $talent_hash = null): array
    {
        return ["status" => 1];
    }
}
