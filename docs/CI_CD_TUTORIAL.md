# CI/CD Tutorial: Auto-Deploy on Push to Production

This guide explains how the deployment workflow works and how to set up similar pipelines for any project.

---

## Table of Contents
1. [How It Works](#how-it-works)
2. [One-Time Setup](#one-time-setup)
3. [How to Use](#how-to-use)
4. [Concepts for Future Projects](#concepts-for-future-projects)
5. [Troubleshooting](#troubleshooting)

---

## How It Works

**Important: Two Separate Environments**

| Step | Where it runs | Env / config |
|------|----------------|--------------|
| **Test job** | GitHub's cloud ( ephemeral) | Uses `.env.example` + generated `APP_KEY`. phpunit.xml overrides DB/cache/session for tests. No production secrets. |
| **Deploy job** | Your server (via SSH) | Uses `.env.production` already on the server. Never touches `.env.example`. |

The deploy step does **not** copy `.env.example` or use CI env. It SSHs to your server and runs `./deploy.sh`, which uses the real `.env.production` on the server. All production keys (DB, Redis, APP_KEY, etc.) live there.

```
┌─────────────────┐     push      ┌──────────────────┐
│  Your computer  │ ───────────►  │  GitHub (remote) │
│  git push       │  production   │  receives code   │
└─────────────────┘               └────────┬─────────┘
                                           │
                                           │ triggers
                                           ▼
                                  ┌────────────────────┐
                                  │  GitHub Actions     │
                                  │  (runs in cloud)    │
                                  └────────┬────────────┘
                                           │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
                    ▼                     │                     │
           ┌────────────────┐            │            ┌────────────────┐
           │  Job 1: Test    │            │            │  Job 2: Deploy  │
           │  - Install deps │  pass      │            │  - SSH to      │
           │  - Run tests    │ ───────────┼──────────► │    server      │
           │  - Build assets │            │            │  - Run deploy.sh│
           └────────────────┘   fail      │            └────────────────┘
                    │                     │                     │
                    └─────────────────────┼─────────────────────┘
                              ▲           │           ▲
                              │           │           │
                         Stop here    Deploy runs   Server gets
                         (no deploy)  (only if      new code
                                      tests pass)
```

**Key idea:** When you push to the `production` branch, GitHub Actions:
1. Runs your test suite in the cloud
2. If tests pass → SSHs into your server and runs `./deploy.sh`
3. If tests fail → Stops. No deployment happens.

---

## Why This Works Without Production Keys in CI

**Test job** (runs in GitHub's cloud):
- Copies `.env.example` → `.env` and runs `php artisan key:generate`
- Tests use `phpunit.xml` overrides: in-memory SQLite, array cache, no Redis
- No production DB, CAM_DB, or API keys are needed
- The prepare step uses `CACHE_STORE=array` and `SESSION_DRIVER=array` so `key:generate` doesn't need a database

**Deploy job** (runs on your server):
- Only SSHs in and runs `./deploy.sh`
- `deploy.sh` uses `.env.production` (already on the server with real keys)
- Docker Compose loads `env_file: .env.production`
- Production credentials never go through GitHub Actions

---

## One-Time Setup

### Step 1: Generate an SSH Key for GitHub Actions

On your **local machine** (or a secure place), generate a dedicated key for CI/CD:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy -N ""
```

This creates:
- `~/.ssh/github_actions_deploy` (private key) → goes to GitHub Secrets
- `~/.ssh/github_actions_deploy.pub` (public key) → goes on your server

### Step 2: Add the Public Key to Your Server

On your **production server**:

```bash
# Add the public key to the deploy user's authorized_keys
echo "YOUR_PUBLIC_KEY_CONTENT" >> ~/.ssh/authorized_keys
```

Or copy the contents of `github_actions_deploy.pub` and append to `~/.ssh/authorized_keys` of the user that runs deployments.

### Step 3: Add GitHub Repository Secrets

In your GitHub repo: **Settings → Secrets and variables → Actions → New repository secret**

Create these secrets:

| Secret Name      | Value                                       | Example                    |
|------------------|---------------------------------------------|----------------------------|
| `SSH_HOST`       | Your server IP or hostname                  | `123.45.67.89` or `app.example.com` |
| `SSH_USERNAME`   | Linux user that runs deploy                 | `deploy` or `root`        |
| `SSH_PRIVATE_KEY`| Full content of the private key file        | `-----BEGIN OPENSSH...`    |
| `SSH_PORT`       | (Optional) SSH port; omit for default 22   | `22`                       |
| `DEPLOY_PATH`    | (Optional) Path to project on server       | `/var/www/porngurucam`    |
| `SSH_FINGERPRINT`| (Recommended) Server's SSH host key fingerprint | Run command below to get it |

**Get your server's SSH fingerprint** (run on your server or use `ssh-keyscan`):
```bash
ssh-keyscan -t ed25519 your-server.com 2>/dev/null | ssh-keygen -lf -
# Or for the server's own key file:
ssh your-server.com "ssh-keygen -l -f /etc/ssh/ssh_host_ed25519_key.pub" | awk '{print $2}'
```

**To copy the private key:**
```bash
cat ~/.ssh/github_actions_deploy
```
Copy the entire output including `-----BEGIN` and `-----END` lines.

### Step 4: Ensure deploy.sh Is Executable

On the server:
```bash
chmod +x /var/www/porngurucam/deploy.sh
```

### Step 5: Ensure .env.production Exists

The deploy script expects `.env.production` on the server. Copy from `.env.production.example` and configure it before the first deploy.

---

## How to Use

### Deploy to Production

```bash
# Make sure you're on production (or merge into it)
git checkout production
git pull origin production

# Make your changes, or merge from main
git merge main   # if you develop on main

# Push – this triggers the workflow
git push origin production
```

Then:
1. Go to **GitHub → Actions**
2. Watch the "Deploy to Production" workflow run
3. Tests run first (~1–2 min)
4. If tests pass, deploy runs (~3–5 min for Docker build)
5. Check your site: https://pornguru.cam

### Branches in This Project

| Branch      | Purpose                          | CI Runs                   |
|-------------|----------------------------------|---------------------------|
| `main`      | Development / feature branches   | Lint, Tests               |
| `develop`   | Staging / integration            | Lint, Tests               |
| `production`| Live production                  | Tests → Deploy            |

---

## Concepts for Future Projects

### 1. Workflow Triggers

```yaml
on:
  push:
    branches: [production]   # When to run
```

Common patterns:
- `branches: [main]` – every push to main
- `branches: [production, staging]` – multiple branches
- `pull_request: branches: [main]` – on PRs
- `workflow_dispatch:` – manual trigger from Actions tab

### 2. Jobs and Dependencies

```yaml
jobs:
  test:
    runs-on: ubuntu-latest
    steps: [...]

  deploy:
    needs: test              # Only runs if test succeeds
    runs-on: ubuntu-latest
    steps: [...]
```

`needs: test` ensures deploy only runs when tests pass.

### 3. Secrets vs Variables

| Type     | Use for                          | Example                    |
|----------|-----------------------------------|----------------------------|
| **Secrets** | Sensitive data (keys, passwords) | `SSH_PRIVATE_KEY`, `DB_PASSWORD` |
| **Variables** | Non-sensitive config            | `DEPLOY_PATH`, `APP_ENV`   |

Secrets are masked in logs; variables are visible.

### 4. SSH Deploy Pattern

For any project that deploys to a VPS:

1. **Server has the app** (clone, or rsync)
2. **Deploy script** does the actual work (pull, build, migrate, etc.)
3. **GitHub Actions** SSHs in and runs that script

```
GitHub Actions (cloud)  ──SSH──►  Your Server  ──runs──►  deploy.sh
```

### 5. Alternative: Self-Hosted Runner

Instead of SSH, you can install a GitHub runner on your server:

- Workflow runs *on* the server
- No SSH keys needed
- Runner must stay online and be maintained

Use when you prefer a persistent agent over SSH.

### 6. Reusable Workflow Template

For a new project, copy this minimal deploy workflow:

```yaml
name: Deploy

on:
  push:
    branches: [production]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /path/to/your/project
            ./deploy.sh
```

Add a `test` job with `needs` when you want tests before deploy.

---

## Troubleshooting

### "Permission denied (publickey)"
- Confirm the public key is in `~/.ssh/authorized_keys` on the server
- Confirm the correct `SSH_USERNAME` (the user that owns that file)
- Test manually: `ssh -i github_actions_deploy user@host`

### "Host key verification failed"
The runner doesn't have your server in `known_hosts`. Add the server's fingerprint as a secret:

1. Get the fingerprint (from your machine):
   ```bash
   ssh-keyscan -t ed25519 YOUR_SERVER 2>/dev/null | ssh-keygen -lf - | awk '{print $2}'
   ```
2. Add as secret `SSH_FINGERPRINT`
3. Add to the workflow:
   ```yaml
   with:
     fingerprint: ${{ secrets.SSH_FINGERPRINT }}
   ```

### Deploy runs but server doesn't update
- Check `DEPLOY_PATH` – is it correct?
- On the server: `cd /var/www/porngurucam && git status` – is it a git repo?
- Confirm `deploy.sh` exists and is executable

### Tests pass locally but fail in CI
- Match PHP/Node versions: `tests.yml` uses PHP 8.4, Node 22
- Ensure all required env vars are in `.env.example`
- Check for private Composer packages – they need secrets (e.g. FLUX_USERNAME, FLUX_LICENSE_KEY)

### Skip deploy for some pushes
Use a commit message convention and add a condition:
```yaml
- name: Check if deploy needed
  id: check
  run: |
    if [[ "${{ github.event.head_commit.message }}" == *"[skip deploy]"* ]]; then
      echo "deploy=false" >> $GITHUB_OUTPUT
    else
      echo "deploy=true" >> $GITHUB_OUTPUT
    fi
```

Then add `if: steps.check.outputs.deploy == 'true'` to the deploy step.

---

## Quick Reference

| Action                 | Command / Location                          |
|------------------------|---------------------------------------------|
| Trigger deploy         | `git push origin production`                |
| View workflow runs     | GitHub → Actions tab                        |
| Edit workflow          | `.github/workflows/deploy.yml`              |
| Add secrets            | Repo Settings → Secrets and variables       |
| Manual deploy (server) | `cd /var/www/porngurucam && ./deploy.sh`   |
