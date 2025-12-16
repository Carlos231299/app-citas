module.exports = {
    apps: [{
        name: "whatsapp-api",
        script: "./index.js",
        env: {
            PORT: 3000,
            NODE_ENV: "production",
        }
    }]
}
