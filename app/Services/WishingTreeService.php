<?php

namespace App\Services;

class WishingTreeService
{
    use \App\Traits\SessionValidator;

    private $rewards = [
        'essential_116', 'essential_52_3', 'essential_53', 'essential_29', 'essential_31',
        'material_154', 'material_164', 'material_174', 'essential_07', 'material_184',
        'material_194', 'tokens_50'
    ];

    public function executeService($action, $data): array
    {
        $char_id = $data[0] ?? null;
        $sessionkey = $data[1] ?? null;

        $char = $this->validateSession($char_id, $sessionkey);
        if (!($char instanceof \App\Models\Character)) return $char;

        $this->refreshSpinsIfNeeded($char);

        if ($action === 'getData') {
            return $this->getDataPayload($char);
        }

        if ($action === 'spin') {
            if ($char->wt_spins <= 0) {
                return ['status' => 0, 'result' => 'No spins left.'];
            }
            
            $reward = $this->rewards[array_rand($this->rewards)];
            
            $char->wt_spins -= 1;
            $char->wt_today_spins += 1;
            $char->wt_total_spins += 1;
            $char->save();
            
            return [
                'status' => 1,
                'reward' => [$reward]
            ];
        }

        if ($action === 'reset') {
            if ($char->user->tokens >= 20) {
                $char->user->tokens -= 20;
                $char->user->save();
                
                $char->wt_spins += 1;
                $char->save();
            } else {
                return ['status' => 0, 'result' => 'Not enough tokens.'];
            }
            return $this->getDataPayload($char);
        }

        return ['status' => 1];
    }

    private function refreshSpinsIfNeeded($char)
    {
        if (!$char->wt_last_spin || !\Carbon\Carbon::parse($char->wt_last_spin)->isSameDay(now())) {
            $char->wt_spins = 1;
            $char->wt_today_spins = 0;
            $char->wt_last_spin = now();
            $char->save();
        }
    }

    private function getDataPayload($char)
    {
        return [
            'status' => 1,
            'error' => 0,
            'data' => [
                'id' => 1,
                'char_id' => $char->id,
                'total_spins' => $char->wt_total_spins,
                'spin_left' => $char->wt_spins,
                'acc_id' => $char->user->id,
                'today_spinned' => $char->wt_today_spins
            ],
            'rewards' => $this->rewards
        ];
    }
}
