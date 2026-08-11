# OpenWA — Live (Hostinger + Cloud)

Hostinger **shared hosting** par Docker/OpenWA **nahi** chal sakta. Live Findownn site ko WhatsApp ke liye **alag public OpenWA server** chahiye.

## Architecture

```
Player/Owner → Hostinger (Findownn PHP) → HTTPS → OpenWA (Railway/VPS) → WhatsApp
                         ↑ webhook ←──────────────────────────────────────┘
```

## Option 1 — Render.com (sabse aasaan, free tier)

1. GitHub par project push karo (ya sirf `admin/deploy/openwa` folder alag repo).
2. [Render Dashboard](https://dashboard.render.com/) → **New** → **Blueprint** → `render.yaml` connect karo.
3. Deploy hone ke baad URL milega: `https://findownn-openwa-xxxx.onrender.com`
4. **Logs** kholo → API key copy karo (`owa_k1_...`).
5. Live **Admin → OpenWA**:
   - **Base URL:** `https://findownn-openwa-xxxx.onrender.com` (no trailing slash)
   - **API Key:** logs se
   - **Session ID:** `findownn`
   - **Save** → **Register Webhook** → browser me dashboard kholo → QR scan.

## Option 2 — Railway.app

1. [Railway](https://railway.app/) → New Project → **Deploy from GitHub** (repo select).
2. Root directory: `admin/deploy/openwa` (ya poora repo + Dockerfile path).
3. Public domain generate karo (Settings → Networking → Generate Domain).
4. Base URL = `https://YOUR-APP.up.railway.app`
5. Baaki steps Render jaisa.

## Option 3 — VPS (DigitalOcean, Hetzner, etc.)

```bash
cd admin/deploy/openwa
docker compose up -d --build
docker compose logs -f
```

- Nginx/Caddy se HTTPS reverse proxy lagao port 2785 par.
- Public URL example: `https://wa.findownn.com`
- Firewall: sirf Findownn server IP ko allow karna optional (OpenWA API key auth bhi hai).

## Findownn live admin settings

| Field | Live value |
|-------|------------|
| Base URL | `https://YOUR-OPENWA-DOMAIN` (never `localhost`) |
| API Key | OpenWA startup logs |
| Session ID | `findownn` |
| Webhook URL | Auto — `https://yoursite.com/api/v1/openwa/webhook` |

## Cron (booking reminders on Hostinger)

Hostinger **Cron Jobs** → hourly:

```
curl -s "https://YOUR-DOMAIN.com/admin/cron/send-booking-reminders.php?key=YOUR_CRON_SECRET"
```

`CRON_SECRET` = `admin/.env` me.

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Live admin shows localhost warning | Base URL me public HTTPS URL daalo, Save |
| Test Connection fails | OpenWA service sleep (Render free) — pehli request slow; retry |
| Webhook fails | Webhook URL HTTPS hona chahiye; Register Webhook dubara click |
| QR disconnect | OpenWA volume persist karo (VPS/Railway disk) |
