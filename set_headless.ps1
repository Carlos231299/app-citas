$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

# Replace headless: false with headless: true
$c = $c -replace 'headless: false', 'headless: true'

Set-Content $path $c
Write-Host "Switched to headless mode."
