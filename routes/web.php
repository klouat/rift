<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::any('/amf_nl', [\App\Http\Controllers\AmfController::class, 'handle']);
Route::any('/amf_nl/', [\App\Http\Controllers\AmfController::class, 'handle']);
Route::any('/amf_nl/{any}', [\App\Http\Controllers\AmfController::class, 'handle'])->where('any', '.*');

// JSON Library Endpoints (Empty placeholders to prevent Flash Client crashes)
$libraries = [
    'skillLibrary', 'skillEffects', 'weaponEffects', 'backItemEffects',
    'accessoryEffects', 'clothingEffects', 'library', 'missionLibrary',
    'enemyInfo', 'petInfo', 'arenaPetInfo', 'talentSkillLevel', 'petSkillInfo'
];

foreach ($libraries as $lib) {
    if ($lib === 'skillLibrary') {
        Route::get('/skillLibrary', [\App\Http\Controllers\SkillLibraryController::class, 'index']);
        continue;
    }
    if ($lib === 'petInfo') {
        Route::get('/petInfo', [\App\Http\Controllers\PetInfoController::class, 'getPetInfo']);
        continue;
    }
    if ($lib === 'petSkillInfo') {
        Route::get('/petSkillInfo', [\App\Http\Controllers\PetInfoController::class, 'getPetSkillInfo']);
        continue;
    }
    Route::get('/' . $lib, function () {
        return response()->json([]);
    });
}
