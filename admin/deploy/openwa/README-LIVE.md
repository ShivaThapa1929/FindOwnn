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
| **HTTP 502** on Sessions / QR stuck "Starting..." | Render **free tier RAM too low** for Chromium. Set `ENGINE_TYPE=baileys` in Render env → redeploy. Or upgrade to **Starter ($7/mo, 512MB+)** with more headroom. |
| Session stuck "Starting..." / Preparing QR | Click **Kill Stuck** → **Delete** session → create new session after redeploy with `baileys`. |
| Live admin shows localhost warning | Base URL me public HTTPS URL daalo, Save |
| Test Connection fails | Render free sleeps — pehli request 30–60s wait; retry |
| Webhook fails | Webhook URL HTTPS hona chahiye; Register Webhook dubara click |
| QR disconnect after redeploy | Enable **persistent disk** (`/app/data`) in render.yaml |
| OOM / Chromium crash | Use `ENGINE_TYPE=baileys` OR min **2GB RAM** VPS for whatsapp-web.js engine |

### Fix HTTP 502 now (Render dashboard)

1. [Render Dashboard](https://dashboard.render.com/) → **findownn-openwa** → **Environment**
2. Add variable: `ENGINE_TYPE` = `baileys`
3. Add variable: `WWEBJS_AUTH_TIMEOUT_MS` = `120000`
4. **Manual Deploy** → wait for green
5. Open **Sessions** → **Kill Stuck** on `findownn` → **Delete**
6. **+ New Session** → name `findownn` → scan QR
7. Findownn Admin → OpenWA → Test Connection → Send test message
