# How to Push to GitHub - Corruption Control System

## Prerequisites

1. **Git installed** - Download from [git-scm.com](https://git-scm.com)
2. **GitHub account** - Create free account at [github.com](https://github.com)
3. **Git configured** - Set your name and email (first time only)

## Step-by-Step Instructions

### Step 1: Configure Git (First Time Only)

Open Git Bash or Command Prompt and run:

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### Step 2: Create Repository on GitHub

1. Go to [github.com](https://github.com) and log in
2. Click **"+"** icon (top right) → "New repository"
3. Enter:
   - **Repository name**: `corruption-control-system`
   - **Description**: "Online Corruption Complaint Management System"
   - **Public/Private**: Choose (Public for sharing)
   - **Do NOT** check "Initialize with README" (we already have one)
4. Click **"Create repository"**
5. You'll see your repository URL (copy it)

### Step 3: Initialize Local Git Repository

Open Command Prompt/PowerShell and navigate to your project folder:

```bash
cd d:\corruption-control-system
```

Initialize Git:

```bash
git init
```

### Step 4: Add Your Remote Repository

Replace `YOUR_USERNAME` and `YOUR_REPO_NAME` with your actual GitHub username and repository name:

```bash
git remote add origin https://github.com/YOUR_USERNAME/corruption-control-system.git
```

To verify:
```bash
git remote -v
```

### Step 5: Create .gitignore File

Create a `.gitignore` file to exclude unnecessary files:

```bash
# Windows
Thumbs.db
*.exe
*.dll

# PHP
*.log
.DS_Store

# Database & Sensitive Files
includes/config.php.example
*.sql.backup
db_backup/

# IDE
.vscode/
.idea/
*.sublime-project

# Uploads (optional - comment out if you want to include)
uploads/evidence/*
uploads/documents/*
!uploads/evidence/.gitkeep
!uploads/documents/.gitkeep

# Temp files
*.tmp
*.temp
~*
```

Save as `.gitignore` in the root folder.

### Step 6: Add All Files

```bash
git add .
```

Or to add specific files:
```bash
git add *.php css/ js/ db/ includes/ auth/ admin/ citizen/ officer/
```

### Step 7: Create First Commit

```bash
git commit -m "Initial commit: Complete Corruption Control System with all modules"
```

### Step 8: Push to GitHub

**For first push:**
```bash
git branch -M main
git push -u origin main
```

**For subsequent pushes:**
```bash
git push
```

Or with current branch:
```bash
git push origin main
```

## Complete Quick Commands

If you want to do it all at once:

```bash
cd d:\corruption-control-system
git init
git config user.name "Your Name"
git config user.email "your.email@example.com"
git remote add origin https://github.com/YOUR_USERNAME/corruption-control-system.git
git add .
git commit -m "Initial commit: Complete Corruption Control System"
git branch -M main
git push -u origin main
```

## Verify Push Success

1. Go to your GitHub repository URL
2. You should see all your files listed
3. You should see your commit message in the "Commits" tab

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Permission denied (publickey) | [Setup SSH key](https://docs.github.com/en/authentication/connecting-to-github-with-ssh) or use HTTPS with token |
| fatal: Not a git repository | Run `git init` first |
| fatal: 'origin' does not appear to be a git repository | Check `git remote -v` output, re-add origin if needed |
| Error: Your branch is ahead | Just push normally: `git push` |
| SSL certificate error | Run: `git config --global http.sslVerify false` |

## Using HTTPS vs SSH

**HTTPS (Easier for beginners):**
```bash
git remote add origin https://github.com/YOUR_USERNAME/corruption-control-system.git
```

**SSH (More secure, requires setup):**
```bash
git remote add origin git@github.com:YOUR_USERNAME/corruption-control-system.git
```

## Making Future Changes

After initial push, when you make changes:

```bash
git add .
git commit -m "Your commit message describing changes"
git push
```

## Create Development Branch (Optional)

For safer development:

```bash
git checkout -b development
git push -u origin development
```

Then make changes and push to development branch instead of main.

## View Push History

```bash
git log --oneline
```

Or visit your GitHub repository → "Commits" tab

## Create .gitkeep Files (For Empty Directories)

Git doesn't track empty folders. To track them, create `.gitkeep`:

```bash
# Windows
echo. > uploads/evidence/.gitkeep
echo. > uploads/documents/.gitkeep
```

Then:
```bash
git add uploads/
git commit -m "Add upload directories"
git push
```

## Example Repository URL

After pushing, your repository will be at:
```
https://github.com/YOUR_USERNAME/corruption-control-system
```

Share this URL with others to let them access your project.

## Authentication Tips

**If using HTTPS, create a Personal Access Token:**
1. GitHub → Settings → Developer settings → Personal access tokens
2. Generate new token with `repo` scope
3. Use token as password instead of actual GitHub password

**Or use SSH:**
1. Generate SSH key: `ssh-keygen -t ed25519 -C "your.email@example.com"`
2. Add to GitHub in Settings → SSH and GPG keys
3. Use SSH remote URL instead of HTTPS

---

**Congratulations!** Your Corruption Control System is now on GitHub! 🎉
