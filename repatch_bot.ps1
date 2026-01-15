$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

# 1. Re-apply webVersionCache
$old = 'puppeteer: {[\s\S]*?headless: false, // Visible browser[\s\S]*?args: \[''--no-sandbox'', ''--disable-setuid-sandbox''\]\r?\n\s+\}'
$new = 'puppeteer: {
        headless: false, // Visible browser
        args: [''--no-sandbox'', ''--disable-setuid-sandbox'']
    },
    webVersionCache: {
        type: ''remote'',
        remotePath: ''https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html'',
    }'

$c = $c -replace $old, $new

Set-Content $path $c
Write-Host "Patch re-applied."
