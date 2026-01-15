$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\node_modules\whatsapp-web.js\src\util\Injected\Utils.js'
$c = Get-Content $path -Raw

# Escape special characters for regex if needed, but simple replace might work for exact string
$old = 'window.Store.SendSeen.sendSeen(chat);'
$new = 'try { window.Store.SendSeen.sendSeen(chat); } catch (e) { console.error("Logged suppressed error in sendSeen"); }'

if ($c.Contains($old)) {
    $c = $c.Replace($old, $new)
    Set-Content $path $c
    Write-Host "Library patched: sendSeen wrapped in try-catch."
}
else {
    Write-Host "Target string not found for patching."
}
