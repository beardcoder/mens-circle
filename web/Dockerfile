# Astro SSR Frontend Dockerfile mit Bun
FROM oven/bun:1-alpine AS base
WORKDIR /app

# --- Dependencies ---
FROM base AS deps
COPY package.json bun.lock* ./
RUN bun install

# --- Builder ---
FROM base AS builder
COPY --from=deps /app/node_modules ./node_modules
COPY . .

ARG PUBLIC_UMAMI_ENABLED=false
ARG PUBLIC_UMAMI_WEBSITE_ID
ARG PUBLIC_UMAMI_SCRIPT_URL

ENV PUBLIC_UMAMI_ENABLED=${PUBLIC_UMAMI_ENABLED}
ENV PUBLIC_UMAMI_WEBSITE_ID=${PUBLIC_UMAMI_WEBSITE_ID}
ENV PUBLIC_UMAMI_SCRIPT_URL=${PUBLIC_UMAMI_SCRIPT_URL}

RUN bun run build

# --- Production Runner ---
FROM oven/bun:1-alpine AS runner
RUN apk add --no-cache dumb-init

WORKDIR /app

ENV NODE_ENV=production
ENV HOST=0.0.0.0
ENV PORT=4400

COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

RUN addgroup -g 1001 -S nodejs && \
    adduser -S astro -u 1001 && \
    chown -R astro:nodejs /app

USER astro

EXPOSE 4400

ENTRYPOINT ["dumb-init", "--"]
CMD ["bun", "run", "./dist/server/entry.mjs"]
