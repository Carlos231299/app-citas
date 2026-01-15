$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\node_modules\whatsapp-web.js\src\util\Injected\Utils.js'
$c = Get-Content $path -Raw

# The broken string from the error
$broken = ' try { window.Store.SendSeen.sendSeen(chat); } catch (e) { console.error("Logged suppressed error in sendSeen"); }'

# The fix: append .catch() to the promise chain instead
# We don't include 'await' in $broken because it wasn't part of the previous replacement, so it sits outside.
# Original: await window.Store.SendSeen.sendSeen(chat);
# Current:  await try { ... }
# Target:   await window.Store.SendSeen.sendSeen(chat).catch(e => { ... });

$fix = ' window.Store.SendSeen.sendSeen(chat).catch(e => { console.error("Logged suppressed error in sendSeen"); });'

# Perform replacement
if ($c.Contains($broken)) {
    $c = $c.Replace($broken, $fix)
    Set-Content $path $c
    Write-Host "Syntax error fixed."
}
else {
    Write-Host "Broken string not found. Checking for manual variations or previously fixed state."
    # Fallback: try to find the 'await try' sequence just in case whitespace differs
    if ($c -match 'await\s+try') {
        $c = $c -replace 'try\s+\{\s+window\.Store\.SendSeen\.sendSeen\(chat\);\s+\}\s+catch\s+\(e\)\s+\{.*?\s+\}', 'window.Store.SendSeen.sendSeen(chat).catch(e => {})'
        Set-Content $path $c
        Write-Host "Syntax error fixed via regex."
    }
}
