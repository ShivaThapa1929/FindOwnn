# OpenWA Setup — Findownn

## 1. Database (already done if you ran setup)

```bash
php admin/setup-openwa.php
php admin/run-players-migration.php
php admin/_configure-openwa.php
```

## 2. Install OpenWA server

OpenWA is a **separate service** (not on Hostinger shared hosting).

| Environment | Where OpenWA runs | Base URL |
|-------------|-------------------|----------|
| **Local XAMPP** | Your PC (Docker or Node) | `http://localhost:2785` |
| **Live (Hostinger)** | Render / Railway / VPS | `https://your-openwa.onrender.com` |

### Local — Docker

```bash
docker run -d --name openwa -p 2785:2785 rmyndharis/openwa
docker logs openwa
```

### Live — Render.com (recommended)

1. Push repo to GitHub
2. Render → New Blueprint → `admin/deploy/openwa/render.yaml`
3. Copy deploy URL + API key from logs
4. Live Admin → OpenWA → paste HTTPS Base URL → Save → Register Webhook → QR scan

Full live guide: **`admin/deploy/openwa/README-LIVE.md`**

### Local — npm (from source)

```bash
git clone https://github.com/rmyndharis/OpenWA.git
cd OpenWA
npm install
npm run start
```

Default URL: `http://localhost:2785`  
Swagger: `http://localhost:2785/api/docs`

## 3. Admin panel

1. Login as **Super Admin**
2. Go to **Admin → OpenWA**
3. Set:
   - **Base URL:** `http://localhost:2785`
   - **API Key:** from OpenWA startup logs
   - **Session ID:** `findownn`
4. Click **Save OpenWA Settings**
5. **Test Connection**
6. **Register Webhook**

## 4. Create WhatsApp session

In OpenWA dashboard or via API:

```bash
curl -X POST http://localhost:2785/api/sessions \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"name": "findownn"}'
```

Scan QR code in OpenWA Web Dashboard to link WhatsApp.

## 5. Automated booking reminders (cron)

Windows Task Scheduler (hourly):

```
php c:\xampp\htdocs\findownn_website\admin\cron\send-booking-reminders.php
```

Or via URL (uses `CRON_SECRET` from admin/.env):

```
https://yoursite.com/admin/cron/send-booking-reminders.php?key=YOUR_CRON_SECRET
```

## Webhook URL (for OpenWA)

```
https://yoursite.com/api/v1/openwa/webhook
```

Local XAMPP:

```
http://localhost/findownn_website/api/v1/openwa/webhook
```
