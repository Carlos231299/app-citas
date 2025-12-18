
use App\Models\User;
use App\Models\Service;
use App\Models\Barber;

// 1. Fix Admin Email
$admin = User::where('email', 'cbastidas52@gmial.com')->first();
if ($admin) {
    $admin->update(['email' => 'cbastidas52@gmail.com']);
    echo "Admin email updated to gmail.com\n";
} else {
    echo "Admin user not found (or already updated)\n";
}

// 2. Seed Services (Idempotent)
// Using exact list from DatabaseSeeder
$services = [
    ['name' => 'Corte', 'price' => 15000, 'extra_price' => 0, 'icon' => 'scissors'],
    ['name' => 'Corte + Barba', 'price' => 20000, 'extra_price' => 0, 'icon' => 'person-badge'],
    ['name' => 'Corte Niño', 'price' => 12000, 'extra_price' => 0, 'icon' => 'emoji-smile'],
    ['name' => 'Barba', 'price' => 10000, 'extra_price' => 0, 'icon' => 'bezier2'],
    ['name' => 'Corte + Cejas', 'price' => 17000, 'extra_price' => 0, 'icon' => 'eye'],
    ['name' => 'Barba + Cerquillo', 'price' => 12000, 'extra_price' => 0, 'icon' => 'brush'],
    ['name' => 'Mascarilla + Masaje', 'price' => 15000, 'extra_price' => 0, 'icon' => 'droplet'],
    ['name' => 'Otro servicio', 'price' => 0, 'extra_price' => 0, 'icon' => 'stars'],
];

foreach ($services as $svc) {
    Service::updateOrCreate(['name' => $svc['name']], $svc);
}
echo "Services seeded.\n";

// 3. Ensure a Barber exists (for testing)
if (Barber::count() == 0) {
    Barber::create([
        'name' => 'Barbero Principal',
        'email' => 'barber@example.com',
        'phone' => '1234567890',
        'bio' => 'Experto en cortes clásicos',
        'is_active' => true
    ]);
    echo "Default Barber created.\n";
}
