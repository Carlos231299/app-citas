$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

# 1. Remove webVersionCache
$c = $c -replace ',\s+webVersionCache: \{[\s\S]*?\}', ''

# 2. Add a delay in the notification loop
$c = $c -replace 'await client.sendMessage\(chatId, message\);', 'await new Promise(resolve => setTimeout(resolve, 2000)); if (!isClientReady) { console.log("⏳ Bot not ready. Skipping message."); return; } await client.sendMessage(chatId, message);'

Set-Content $path $c
Write-Host "Cleanup and delay added."
