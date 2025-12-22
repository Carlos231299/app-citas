<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use Illuminate\Support\Facades\DB;

echo "DB: " . config('database.default') . "\n";

// EMERGENCY SCHEMA FIX
try {
    \Illuminate\Support\Facades\Schema::table('services', function ($table) {
        $table->integer('sort_order')->default(0);
    });
    echo "Force Added sort_order column.\n";
} catch (\Exception $e) {
    echo "Schema fix skipped/failed: " . $e->getMessage() . "\n";
}

echo "--- CURRENT DB STATE ---\n";
foreach(Service::orderBy('id')->get() as $s) {
    echo "ID:{$s->id} | Name:{$s->name} | Order:{$s->sort_order}\n";
}

// FORCE UPDATE OTRO
// Using strict DB update to ignore model events/timestamps if any issue
$affected = DB::table('services')
    ->where('name', 'LIKE', '%ot%ro%') // Matches Otro, otro, OTRO...
    ->update(['sort_order' => 900]);

echo "Updated 'Otro' matches: {$affected}\n";

// FORCE UPDATE CERQUILLOS
$cerq = Service::where('name', 'Cerquillos')->first();
if (!$cerq) {
    echo "Creating Cerquillos...\n";
    $cerq = new Service();
    $cerq->name = 'Cerquillos';
    $cerq->price = 5000;
    $cerq->icon = 'scissors';
    $cerq->save();
}
// Force update specifically
DB::table('services')->where('id', $cerq->id)->update(['sort_order' => 800]);
echo "Updated Cerquillos (ID {$cerq->id}) to 800\n";


echo "--- FINAL DB STATE ---\n";
foreach(Service::orderBy('sort_order')->get() as $s) {
    echo "Order:{$s->sort_order} | {$s->name}\n";
}
