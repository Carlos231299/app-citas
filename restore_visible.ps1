$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

# Replace headless: true with headless: false
$c = $c -replace 'headless: true', 'headless: false'

Set-Content $path $c
Write-Host "Switched to visible mode."
