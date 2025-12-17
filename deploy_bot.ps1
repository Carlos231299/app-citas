# Deploy Bot to EC2
$pemPath = "pruebas.pem"
$server = "ubuntu@ec2-50-18-72-244.us-west-1.compute.amazonaws.com"
$localPath = "whatsapp-bot-local"
$zipName = "whatsapp-bot.zip"
$remoteDir = "/home/ubuntu/whatsapp-bot"

Write-Host "📦 Zipping bot..."
Compress-Archive -Path "$localPath\*" -DestinationPath $zipName -Force

Write-Host "🚀 Uploading to EC2..."
scp -i $pemPath -o StrictHostKeyChecking=no $zipName $server:/home/ubuntu/$zipName

Write-Host "🔧 Installing on EC2..."
$commands = @(
    "rm -rf $remoteDir",
    "mkdir -p $remoteDir",
    "unzip -o whatsapp-bot.zip -d $remoteDir",
    "cd $remoteDir",
    "rm -rf node_modules",
    "npm install",
    "npm install typescript ts-node -D" 
)
# Note: User might need to run system dep install manually if it fails, but let's try basic first.
# Also need to make sure Puppeteer downloads Chrome on linux.

$cmdStr = $commands -join " && "
ssh -i $pemPath -o StrictHostKeyChecking=no $server "$cmdStr"

Write-Host "✅ Deployment files ready. Run 'npm run start' inside '$remoteDir' on the server."
Remove-Item $zipName
