// Revert Lucide Icons back to Bootstrap Icons
echo "Reverting icons...\n";

// Revert exact map
\App\Models\Service::where('icon', 'user')->update(['icon' => 'person']);
\App\Models\Service::where('icon', 'smile')->update(['icon' => 'emoji-smile']);
\App\Models\Service::where('icon', 'sparkles')->update(['icon' => 'stars']);
\App\Models\Service::where('icon', 'droplet')->update(['icon' => 'droplet-fill']);
\App\Models\Service::where('icon', 'message-circle')->update(['icon' => 'whatsapp']);
\App\Models\Service::where('icon', 'video')->update(['icon' => 'tiktok']);
\App\Models\Service::where('icon', 'camera')->update(['icon' => 'instagram']);
\App\Models\Service::where('icon', 'facebook')->update(['icon' => 'facebook']); // assuming consistent

// Partial string replacements
\App\Models\Service::query()->get()->each(function($service) {
    if (str_contains($service->icon, 'user')) {
        $service->update(['icon' => str_replace('user', 'person', $service->icon)]);
    }
    if (str_contains($service->icon, 'smile')) {
        $service->update(['icon' => str_replace('smile', 'emoji-smile', $service->icon)]);
    }
    if (str_contains($service->icon, 'sparkles')) {
        $service->update(['icon' => str_replace('sparkles', 'stars', $service->icon)]);
    }
    if (str_contains($service->icon, 'droplet')) {
        $service->update(['icon' => str_replace('droplet', 'droplet-fill', $service->icon)]); 
    }
});

echo "Icons reverted successfully.\n";
