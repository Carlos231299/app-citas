$path = 'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\whatsapp-bot-local\index.ts'
$c = Get-Content $path -Raw

# Remove invalid webVersionCache block
$c = $c -replace ',\s+webVersionCache: \{[\s\S]*?\}', ''

Set-Content $path $c
Write-Host "Cleanup completed. Invalid cache removed."
