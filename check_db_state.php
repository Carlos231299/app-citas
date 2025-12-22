<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Database Default: " . config('database.default') . PHP_EOL;
echo "Database Connection: " . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . PHP_EOL;

$exists = \Illuminate\Support\Facades\Schema::hasColumn('services', 'sort_order');
echo "Column sort_order exists: " . ($exists ? 'YES' : 'NO') . PHP_EOL;

if (!$exists) {
    echo "Attempting to create column manually via Schema..." . PHP_EOL;
    try {
        \Illuminate\Support\Facades\Schema::table('services', function ($table) {
             $table->integer('sort_order')->default(0);
        });
        echo "Column created successfully." . PHP_EOL;
    } catch (\Exception $e) {
        echo "Error creating column: " . $e->getMessage() . PHP_EOL;
    }
}
