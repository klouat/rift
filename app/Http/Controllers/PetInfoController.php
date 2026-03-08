<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PetInfoController extends Controller
{
    public function getPetInfo()
    {
        $path = base_path('petInfo.json');
        if (!File::exists($path)) {
            return response()->json([]);
        }

        $data = json_decode(File::get($path), true);
        // PetInfo.as expects: { "savedPetInfo": [...] } or equivalent
        return response()->json($data);
    }

    public function getPetSkillInfo()
    {
        $path = base_path('petInfo.json');
        if (!File::exists($path)) {
            return response()->json([]);
        }

        $data = json_decode(File::get($path), true);
        // The client currently expects the same structure for both if we use this file
        return response()->json($data);
    }
}
