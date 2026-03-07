<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BattleService
{
    /**
     * Client-side global error reporter (main.as:388).
     * Called at most 3 times per session for uncaught AS errors.
     * Response is completely ignored — callback is `function(param1:*):* {}`.
     *
     * Args: message, stackTrace, place, char_id
     */
    public function logError($message, $stack_trace, $place, $char_id): array
    {
        Log::warning('Client AS error', [
            'char_id'     => $char_id,
            'place'       => $place,
            'message'     => $message,
            'stack_trace' => $stack_trace,
        ]);

        return ['status' => 1];
    }
}
