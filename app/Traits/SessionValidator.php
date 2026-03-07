<?php

namespace App\Traits;

use App\Models\Character;
use Illuminate\Support\Facades\Log;

trait SessionValidator
{
    /**
     * Validates if the character exists and the session key matches the user's stored session key.
     *
     * @param int|string $char_id
     * @param string $sessionkey
     * @param array $mismatchResponse Default response on session mismatch
     * @return Character|array Returns the Character object if valid, otherwise an error response array.
     */
    protected function validateSession($char_id, $sessionkey, array $mismatchResponse = ['status' => 2, 'result' => 'Session mismatch'])
    {
        $char = Character::with('user')->find((int) $char_id);

        if (!$char) {
            return ['status' => 2, 'result' => 'Character not found'];
        }

        if ($char->user->sessionkey !== $sessionkey) {
            return $mismatchResponse;
        }

        return $char;
    }
}
