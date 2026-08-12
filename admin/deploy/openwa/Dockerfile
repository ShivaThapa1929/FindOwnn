# Findownn — OpenWA gateway (live / VPS / Railway / Render)
# Default ENGINE_TYPE=baileys: works on Render free tier (no Chromium OOM / HTTP 502)
FROM rmyndharis/openwa:latest

ENV ENGINE_TYPE=baileys
ENV WWEBJS_AUTH_TIMEOUT_MS=120000
ENV NODE_ENV=production

EXPOSE 2785

# Persist WhatsApp session data (mount a volume at /app/data on VPS/Render disk)
VOLUME ["/app/data"]
