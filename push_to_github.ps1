# PowerShell Script to Push to GitHub
# Save this as push_to_github.ps1 and run it

cd d:\corruption-control-system

git init

git config user.name "tn-prince-yt"
git config user.email "elavarasan.s.official@gmail.com"

git remote add origin https://github.com/tn-prince-yt/Corruption-Control-System.git

git add .

git commit -m "Initial commit: Complete Corruption Control System with Citizen, Admin, and Officer modules"

git branch -M main

git push -u origin main

Write-Host "Push completed! Check your repository at: https://github.com/tn-prince-yt/Corruption-Control-System"