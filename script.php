<?php

// script.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\CharacterItem::whereIn('item_type', ['char_hairs', 'char_sets'])->get();
foreach ($items as $item) {
    if (!preg_match('/_[01]$/', $item->item_id)) {
        $char = $item->character;
        if ($char) {
            $item->item_id .= '_' . $char->gender;
            $item->save();
        }
    }
}
echo "Fixed!\n";

