# Deployment Guide

This document outlines the deployment process and post-deployment tasks for TeleMed Pro.

## Pre-Deployment

1. **Run Tests**
   ```bash
   php artisan test --no-coverage
   ```
   Ensure all tests pass before deploying.

2. **Build Frontend Assets**
   ```bash
   npm run build
   ```

3. **Review Migrations**
   ```bash
   php artisan migrate --pretend
   ```

## Deployment Steps

1. **Pull Latest Code**
   ```bash
   git pull origin main
   ```

2. **Install Dependencies**
   ```bash
   composer install --no-dev
   npm ci
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

4. **Clear Caches**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Post-Deployment Tasks

### Agent Referral Links Setup

After deploying the referral links feature, create referral links for all agents:

```bash
php artisan agent:create-referral-links
```

**What it does:**
- Creates 3 referral links per agent (Patient, Agent, Business)
- Generates unique referral codes and tokens for each link
- Skips agents that already have referral links

**Options:**
- Create for a specific agent:
  ```bash
  php artisan agent:create-referral-links --agent-id=123
  ```

**Expected Output:**
```
Creating referral links for X agents...
✓ Created referral links for Agent Name 1
✓ Created referral links for Agent Name 2
...
Done!
```

## Rollback

If issues occur during deployment:

```bash
php artisan migrate:rollback
git reset --hard HEAD~1
```

## Monitoring

After deployment, monitor:
- Application logs: `storage/logs/laravel.log`
- Queue jobs: `php artisan queue:failed`
- Database connections and performance

