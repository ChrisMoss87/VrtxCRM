# Nginx Multi-Tenant Configuration Guide

## Problem

Accessing `http://acme.vrtxcrm.local` shows the default "Welcome to nginx!" page.

## Quick Fix

Run this script:
```bash
./setup-nginx.sh
```

Then test:
```bash
curl http://acme.vrtxcrm.local
```

---

## Files

- **Configuration**: `nginx-config.conf` (copy to `/etc/nginx/sites-available/VrtxCRM`)
- **Setup Script**: `setup-nginx.sh` (automated setup)
- **This Guide**: `NGINX_SETUP.md`

---

## What the Setup Does

1. Backs up existing config
2. Copies new multi-tenant config
3. Disables conflicting configs (Vrtx, vrtxCRM)
4. Enables VrtxCRM config
5. Tests and reloads nginx

---

## Test After Setup

```bash
# Should return Laravel, not nginx default
curl http://acme.vrtxcrm.local

# View in browser
open http://acme.vrtxcrm.local
```
