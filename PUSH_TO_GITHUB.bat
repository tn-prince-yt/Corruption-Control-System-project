# GitHub Push Commands - Ready to Run

## Step 1: Install Git First
Download and install Git from: https://git-scm.com/download/win

## Step 2: After Installation, Run These Commands

Open PowerShell and run these commands one by one:

```powershell
cd d:\corruption-control-system

git init

git config user.name "tn-prince-yt"
git config user.email "elavarasan.s.official@gmail.com"

git remote add origin https://github.com/tn-prince-yt/Corruption-Control-System.git

git add .

git commit -m "Initial commit: Complete Corruption Control System with Citizen, Admin, and Officer modules"

git branch -M main

git push -u origin main
```

## Step 3: Authentication
When prompted for credentials:
- **Username**: `tn-prince-yt`
- **Password**: Your GitHub Personal Access Token (create at https://github.com/settings/tokens)

## Quick One-Line Command (After Git Installation)

```powershell
cd d:\corruption-control-system && git init && git config user.name "tn-prince-yt" && git config user.email "elavarasan.s.official@gmail.com" && git remote add origin https://github.com/tn-prince-yt/Corruption-Control-System.git && git add . && git commit -m "Initial commit: Complete Corruption Control System" && git branch -M main && git push -u origin main
```

## If You Get Authentication Errors

1. Go to https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Give it a name like "Corruption Control System"
4. Select `repo` scope
5. Generate and copy the token
6. Use this token as your password when pushing

## Verify Success

After pushing, visit: https://github.com/tn-prince-yt/Corruption-Control-System

You should see all your project files there! 🎉