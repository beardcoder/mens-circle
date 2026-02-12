# Payload CMS + Next.js Dockerfile mit Bun
FROM oven/bun:1-alpine AS base
WORKDIR /app

# --- Dependencies ---
FROM base AS deps
RUN apk add --no-cache vips-dev build-base python3
COPY package.json bun.lock* ./
RUN bun install

# --- Builder ---
FROM base AS builder
RUN apk add --no-cache vips-dev build-base python3
COPY --from=deps /app/node_modules ./node_modules
COPY . .

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN bun run build

# --- Production Runner ---
FROM oven/bun:1-alpine AS runner
RUN apk add --no-cache vips dumb-init

WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV PORT=3001
ENV HOSTNAME=0.0.0.0

COPY --from=builder /app/.next ./.next
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json
COPY --from=builder /app/next.config.mjs ./next.config.mjs

# Media-Upload Verzeichnis
RUN mkdir -p /app/media

RUN addgroup -g 1001 -S nodejs && \
    adduser -S payload -u 1001 && \
    chown -R payload:nodejs /app

USER payload

EXPOSE 3001

ENTRYPOINT ["dumb-init", "--"]
CMD ["sh", "-c", "bunx payload migrate && bun run start"]
