// Map Bootstrap Icons to Lucide (Run via Tinker)
echo "Migrating icons...\n";

// exact replacements first
\App\Models\Service::where('icon', 'bi-person')->update(['icon' => 'user']);
\App\Models\Service::where('icon', 'bi-scissors')->update(['icon' => 'scissors']);

// partial string replacements
\App\Models\Service::query()->get()->each(function($service) {
    if (str_contains($service->icon, 'person')) {
        $service->update(['icon' => str_replace('person', 'user', $service->icon)]);
    }
    if (str_contains($service->icon, 'emoji-smile')) {
        $service->update(['icon' => str_replace('emoji-smile', 'smile', $service->icon)]);
    }
    if (str_contains($service->icon, 'stars')) {
        $service->update(['icon' => str_replace('stars', 'sparkles', $service->icon)]);
    }
    if (str_contains($service->icon, 'droplet-fill')) {
        $service->update(['icon' => str_replace('droplet-fill', 'droplet', $service->icon)]);
    }
    // Brands not in standard Lucide
    if (str_contains($service->icon, 'whatsapp')) {
        $service->update(['icon' => str_replace('whatsapp', 'message-circle', $service->icon)]);
    }
    if (str_contains($service->icon, 'tiktok')) {
        $service->update(['icon' => str_replace('tiktok', 'video', $service->icon)]);
    }
    if (str_contains($service->icon, 'instagram')) {
        $service->update(['icon' => str_replace('instagram', 'camera', $service->icon)]);
    }
    if (str_contains($service->icon, 'facebook')) {
        $service->update(['icon' => str_replace('facebook', 'facebook', $service->icon)]); // facebook exists in some sets, verify? fallback to thumbs-up
    }
    
    // Clean up
    $service->update(['icon' => str_replace('bi-', '', $service->icon)]);
});

echo "Icons updated successfully.\n";
