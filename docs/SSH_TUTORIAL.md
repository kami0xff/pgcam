# SSH Tutorial for Beginners

A gentle introduction to SSH (Secure Shell): what it is, why it matters, and how to use it without drowning in jargon.

---

## What is SSH?

**SSH** lets you securely control a remote computer over the internet.

Think of it like this:
- Without SSH: You're at your desk, and the server is in a datacenter somewhere. You can't walk over and use its keyboard.
- With SSH: You open a "tunnel" from your computer to the server. You type commands on your machine, but they run on the server. It's like having a very long keyboard cable.

**"Secure"** means everything is encrypted. Other people on the network can't see what you're doing.

---

## When You’ll Use SSH

- **Deploying** – Run commands on your production server (like `./deploy.sh`)
- **Debugging** – Check logs, restart services, see what’s failing
- **Git repos** – Push/pull from GitHub/GitLab using SSH instead of HTTPS
- **CI/CD** – GitHub Actions or similar tools SSH into your server to deploy

---

## Prerequisites

- A **terminal** (Terminal.app on Mac, Windows Terminal or PowerShell on Windows)
- A **remote machine** – a VPS (DigitalOcean, Linode, etc.) or any Linux server
- Basic **command line** – knowing `cd`, `ls`, `cat` is enough

---

## Part 1: Logging In with a Password

### Basic login

```bash
ssh username@server-address
```

- `username` – The user account on the **server** (e.g. `root`, `deploy`)
- `server-address` – IP (`192.168.1.1`) or domain (`myserver.com`)

Example:
```bash
ssh root@123.45.67.89
```

The first time you connect, you’ll see something like:

```
The authenticity of host '123.45.67.89' can't be established.
ED25519 key fingerprint is SHA256:xxxxx...
Are you sure you want to continue connecting (yes/no/[fingerprint])?
```

Type `yes` and press Enter. Your computer will remember the server.

Then you’ll be asked for the server’s password. Type it (you won’t see characters) and press Enter.

If all goes well, your prompt changes to something like `root@myserver:~#`. You’re now running commands on the server.

### Logging out

```bash
exit
```

or press `Ctrl+D`.

---

## Part 2: SSH Keys (Passwordless Login)

Typing the password every time is annoying. SSH keys let you log in without entering it.

### How it works

- **Private key** – Lives on your computer. Keep it secret.
- **Public key** – Can be copied to the server. It’s safe to share.
- When you connect, your machine proves it has the private key. The server checks against the public key and lets you in without a password.

### Generating a key pair

```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
```

- `-t ed25519` – Use the modern, secure Ed25519 algorithm  
- `-C` – Optional label (often your email)

You’ll be asked:

1. **Where to save?**  
   Default is `~/.ssh/id_ed25519`. Press Enter to accept.

2. **Passphrase?**  
   - Empty = no passphrase (fast, less secure if someone steals your key)  
   - A passphrase = more secure, but you type it when using the key

For a simple personal server, pressing Enter twice (no passphrase) is common.

Result:

- `~/.ssh/id_ed25519` – private key (never share)
- `~/.ssh/id_ed25519.pub` – public key (safe to give to servers)

### Copying your public key to the server

**Option A: Let SSH do it**

```bash
ssh-copy-id username@server-address
```

Type the server password once. After that, you can log in with:

```bash
ssh username@server-address
```

No password needed.

**Option B: Copy-paste**

1. Show your public key:
   ```bash
   cat ~/.ssh/id_ed25519.pub
   ```
2. On the server:
   ```bash
   mkdir -p ~/.ssh
   echo "paste-your-public-key-here" >> ~/.ssh/authorized_keys
   chmod 700 ~/.ssh
   chmod 600 ~/.ssh/authorized_keys
   ```

### Testing it

```bash
ssh username@server-address
```

If it logs you in without asking for a password, it works.

---

## Part 3: Common Tasks

### Run a single command without a shell

```bash
ssh username@server "ls -la /var/www"
```

Useful for quick checks or scripts.

### Copy files TO the server

```bash
scp myfile.txt username@server:/path/to/destination/
```

### Copy files FROM the server

```bash
scp username@server:/path/to/remote/file.txt ./
```

### Copy a whole folder

```bash
scp -r myfolder username@server:/path/to/destination/
```

---

## Part 4: SSH Config (Shortcuts)

Instead of typing `ssh root@123.45.67.89` every time, you can define shortcuts.

Edit (or create) `~/.ssh/config`:

```
Host myserver
    HostName 123.45.67.89
    User root
    Port 22
```

Now you can just run:

```bash
ssh myserver
```

Same for SCP:

```bash
scp myfile.txt myserver:/var/www/
```

### Extra options in config

```
Host myserver
    HostName myserver.com
    User deploy
    Port 2222
    IdentityFile ~/.ssh/my_custom_key    # Use a specific key
```

---

## Part 5: Multiple Keys

You might use one key for personal servers and another for work or CI/CD.

### Generate a second key

```bash
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions -N ""
```

- `-f` – Custom filename
- `-N ""` – No passphrase

### Use it per host

In `~/.ssh/config`:

```
Host work-server
    HostName work.example.com
    User admin
    IdentityFile ~/.ssh/work_key
```

When you `ssh work-server`, it uses `work_key` automatically.

---

## Part 6: Security Tips (Without Paranoia)

### 1. Protect your private keys

- Never share `id_ed25519` or any file without `.pub`
- Don’t commit them to Git
- Permissions: `chmod 600 ~/.ssh/id_ed25519`

### 2. Disable password login (optional)

On the server, after you’ve confirmed key login works:

```bash
sudo nano /etc/ssh/sshd_config
```

Change:
```
PasswordAuthentication no
```

Then restart SSH:
```bash
sudo systemctl restart sshd
```

Only do this if you’re sure key login works and you won’t lock yourself out.

### 3. Use non-default ports (optional)

Changing port 22 to something else reduces automated attacks:

```
Port 2222
```

Then connect with:
```bash
ssh -p 2222 username@server
```

### 4. Use different keys for different roles

- Personal projects
- Work
- CI/CD (e.g. GitHub Actions)

Separate keys limit damage if one is compromised.

---

## Part 7: Troubleshooting

### "Permission denied (publickey)"

- The server doesn’t recognize your key.
- Check:
  - Public key is in `~/.ssh/authorized_keys` on the server
  - You’re using the right user: `ssh user@host` (user must own that `authorized_keys` file)
  - You’re using the right key: `ssh -i ~/.ssh/my_key user@host` if you have several keys

### "Connection refused"

- Nothing is listening on the SSH port (usually 22).
- Possible causes: wrong IP/host, firewall blocking port 22, SSH service not running.

### "Host key verification failed"

- The server’s “fingerprint” changed or isn’t trusted yet.
- For a server you trust, you can clear old keys:
  ```bash
  ssh-keygen -R server-address
  ```
- Then connect again and accept the new fingerprint.

### "Too many authentication failures"

- You have many keys and SSH is trying them all.
- Use a single key:
  ```bash
  ssh -i ~/.ssh/id_ed25519 user@host
  ```
- Or set `IdentitiesOnly yes` in `~/.ssh/config` for that host.

---

## Quick Reference

| Task | Command |
|------|---------|
| Log in | `ssh user@host` |
| Log in with specific key | `ssh -i ~/.ssh/mykey user@host` |
| Log in on different port | `ssh -p 2222 user@host` |
| Run one command | `ssh user@host "command"` |
| Copy file to server | `scp file user@host:/path/` |
| Copy file from server | `scp user@host:/path/file .` |
| Copy folder | `scp -r folder user@host:/path/` |
| Log out | `exit` or `Ctrl+D` |

---

## What’s next?

- Use `~/.ssh/config` for shortcuts
- Use SSH keys with GitHub/GitLab
- Set up SSH deploy for CI/CD (see [CI/CD Tutorial](CI_CD_TUTORIAL.md))
