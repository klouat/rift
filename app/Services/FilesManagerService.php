<?php

namespace App\Services;

class FilesManagerService
{
    /**
     * Returns a list of SWF files that should be cleared from the client cache.
     * Returning an empty array tells the client nothing needs clearing,
     * so it skips straight to onGameReady(true).
     */
    public function checkClearCache($account_id): array
    {
        return [];
    }

    /**
     * Acknowledges that the client has cleared the listed cache files.
     * Response is not inspected — client calls onGameReady() immediately after.
     */
    public function setCacheCleared($account_id, $files): array
    {
        return ["status" => 1];
    }
}
