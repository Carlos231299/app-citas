$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

# 1. Add isClientReady variable at the beginning
if ($c -notlike '*let isClientReady*') {
    $c = "let isClientReady = false;`r`n" + $c
}

# 2. Update ready event
$c = $c -replace 'client.on\(''ready'', \(\) => \{', "client.on('ready', () => { `r`nisClientReady = true;"

# 3. Add ready check before sendMessage
$c = $c -replace 'await client.sendMessage\(chatId, message\);', 'if (!isClientReady) { console.log("⏳ Bot not ready. Skipping message."); return; } await client.sendMessage(chatId, message);'

# 4. Replace Silent fail with Error logging
$c = $c -replace '(?s)\} catch \(error: any\) \{.*?// Silent fail', '} catch (error: any) { console.error("❌ Polling Error: " + error.message);'

Set-Content $path $c
Write-Host "Update completed."
