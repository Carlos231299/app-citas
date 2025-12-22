import paramiko
import time

def deploy():
    # Server Details (from previous context)
    # The user has "pruebas.pem", suggesting AWS/EC2 usage.
    # We will assume standard default user (ubuntu) or 'user' based on Windows 'c:\Users\Carlos' implying local dev.
    # Wait, the user said "181.214.147.24" in prior convs for the test server? 
    # Or "ec2 instance" in another summary.
    # Let's try the IP "181.214.147.24" first as it's the most specific "test server" mentioned.
    # BUT, that server required a password.
    # If "pruebas.pem" is here, maybe it's for a different server.
    # Let's try to infer from project context.
    # If the user says "Deploy" and "Pruebas.pem" exists...
    
    # REVISITING CONVERSATION HISTORY:
    # "Conversation 9cd7b8b8...: deploy... to a test server (181.214.147.24)... with password authentication".
    # "Conversation 0e67c560...: restart project... on an EC2 instance."
    
    # Given "pruebas.pem" is present in THIS folder, it's safer to use it.
    # I'll try to use the key with user 'ubuntu' (common for EC2) or 'admin'.
    # However, without sticking to a confirmed IP, I should ASK or try the likely one.
    # Wait, I can't ask, the user said "Hazlo tú".
    
    # Let's look for a hardcoded IP in .env?
    pass

# I will check .env first to see if there is any server IP hint.
# Or I will create a script that tries the Paramiko connection to the likely IP.
