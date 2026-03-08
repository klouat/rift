<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $libraryPath = base_path('library.json');
        if (file_exists($libraryPath)) {
            $content = json_decode(file_get_contents($libraryPath), true);
            if (isset($content['savedLibrary'])) {
                foreach ($content['savedLibrary'] as $libItem) {
                    \App\Models\GameLibrary::updateOrCreate(
                        ['item_id' => $libItem['item_id']],
                        ['data' => $libItem['effects'] ?? []]
                    );
                }
            }
        }
    }
}
