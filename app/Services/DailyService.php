<?php

namespace App\Services;

class DailyService
{
    public function executeService($action, $params = [])
    {
        switch ($action) {
            case 'getData':
                return $this->get_data($params);
            case 'setOpenedToday':
            case 'stampDay':
            case 'claimReward':
                return ['status' => 1];
            default:
                return ['status' => 1];
        }
    }

    private function get_data(array $params): array
    {
        $current_day = (int) date('N'); // 1 = Monday … 7 = Sunday
        // If the game expects 0-indexed or 1-indexed differently, we might need adjustment.
        // Based on the log, it returned 6 for Saturday.

        return [
            'status'       => 1,
            'total_days'   => '31',
            'starts_on'    => 'Sunday',
            'current_day'  => $current_day,
            'rewards'      => [
                'essential_51_2', 'tokens_25',
                'essential_51_2', 'tokens_30',
                'essential_125',  'tokens_75',
            ],
            'daily_data'   => [
                [
                    'id'           => 1,
                    'acc_id'       => $params[0] ?? 0,
                    'type'         => $params[2] ?? 'normal',
                    'status'       => '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
                    'claimed'      => '0,0,0,0,0,0',
                    'opened_today' => 0,
                    'stamped_today'=> 0,
                ],
            ],
        ];
    }
}
