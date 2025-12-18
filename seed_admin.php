// Create Admin User using fully qualified names
$user = \App\Models\User::create([
    'name' => 'Carlos23',
    'username' => 'Carlos23',
    'email' => 'cbastidas52@gmial.com',
    'password' => \Illuminate\Support\Facades\Hash::make('Kike2312'), 
    'role' => 'admin'
]);

echo "User Created: " . $user->email . "\n";
