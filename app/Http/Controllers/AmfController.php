<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SabreAMF_CallbackServer;

class AmfController extends Controller
{
    public function handle(Request $request)
    {
        if (!class_exists('SabreAMF_CallbackServer')) {
            require_once base_path('vendor/sabre/amf/lib/SabreAMF/CallbackServer.php');
        }

        $server = new SabreAMF_CallbackServer();

        $server->onInvokeService = function ($service, $method, $data) {
            $safe_args = $this->sanitize_log_args($data);

            Log::info("AMF call: {$service}.{$method}", [
                'args' => $safe_args,
            ]);

            $services = [
                'SystemLogin'      => \App\Services\SystemLogin::class,
                'CharacterService' => \App\Services\CharacterService::class,
                'AC'               => \App\Services\AcService::class,
                'FilesManager'     => \App\Services\FilesManagerService::class,
                'AdvancedAcademy'  => \App\Services\AdvancedAcademyService::class,
                'DailyService'     => \App\Services\DailyService::class,
                'PlayStore'        => \App\Services\PlayStoreService::class,
                'Steam'            => \App\Services\PlayStoreService::class,
                'BattleSystem'     => \App\Services\BattleSystem::class,
                'TalentService'    => \App\Services\TalentService::class,
                'SenjutsuService'  => \App\Services\SenjutsuService::class,
                'Battle'           => \App\Services\BattleService::class,
                'PetService'       => \App\Services\PetService::class,
                'PetArena'         => \App\Services\PetService::class,
                'ScratchCard'      => \App\Services\ScratchCard::class,
                'HuntingHouse'     => \App\Services\HuntingHouse::class,
                'EudemonGarden'    => \App\Services\EudemonGarden::class,
                'LevelUpPackages'  => \App\Services\LevelUpPackagesService::class,
                'ValentineEvent2026' => \App\Services\ValentineEvent2026Service::class,
                'HanamiEvent2026'    => \App\Services\HanamiEvent2026Service::class,
                'MaterialMarket'     => \App\Services\MaterialMarketService::class,
                'Blacksmith'         => \App\Services\BlacksmithService::class,
                'BlacksmithLightning'=> \App\Services\BlacksmithService::class,
                'WishingTree'        => \App\Services\WishingTreeService::class,
                'DailyRoulette'      => \App\Services\DailyRoulette::class,
                'BlackMerchant'      => \App\Services\BlackMerchant::class,
            ];






            try {
                $class = $services[$service] ?? null;

                // Catch-all: unknown service using executeService gets a safe stub
                if (!$class) {
                    if ($method === 'executeService') {
                        Log::warning("AMF stub (unimplemented): {$service}.{$method}", ['args' => $safe_args]);
                        return ['status' => 1];
                    }
                    throw new \Exception("Unknown service: {$service}");
                }

                $instance = app($class);

                if (!method_exists($instance, $method)) {
                    throw new \Exception("Unknown method: {$service}.{$method}");
                }

                $result = call_user_func_array([$instance, $method], $data);

                Log::info("AMF response: {$service}.{$method}", [
                    'result' => $result,
                ]);

                return $result;

            } catch (\Throwable $e) {
                Log::error("AMF error in {$service}.{$method}", [
                    'args'    => $safe_args,
                    'error'   => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                throw $e;
            }
        };

        try {
            ob_start();
            $server->exec();
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            ob_get_clean();
            Log::error('AMF deserialization/server error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response('AMF Error: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }

        return response($output)->header('Content-Type', 'application/x-amf');
    }

    /**
     * Strip sensitive fields (passwords) before logging.
     */
    private function sanitize_log_args(array $args): array
    {
        return array_map(function ($arg) {
            if (is_string($arg) && strlen($arg) > 200) {
                return substr($arg, 0, 200) . '…';
            }
            return $arg;
        }, $args);
    }
}
