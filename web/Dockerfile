# Astro Frontend Dockerfile with Bun
FROM oven/bun:1-alpine AS base

WORKDIR /app

# Install dependencies
FROM base AS deps
COPY package.json bun.lock* ./
RUN bun install --frozen-lockfile

# Build the application
FROM base AS builder
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Set build-time environment variables
ARG CMS_URL
ARG PAYLOAD_API_KEY
ARG SITE_URL
ARG PUBLIC_UMAMI_ENABLED
ARG PUBLIC_UMAMI_WEBSITE_ID
ARG PUBLIC_UMAMI_SCRIPT_URL

ENV CMS_URL=${CMS_URL}
ENV PAYLOAD_API_KEY=${PAYLOAD_API_KEY}
ENV SITE_URL=${SITE_URL}
ENV PUBLIC_UMAMI_ENABLED=${PUBLIC_UMAMI_ENABLED}
ENV PUBLIC_UMAMI_WEBSITE_ID=${PUBLIC_UMAMI_WEBSITE_ID}
ENV PUBLIC_UMAMI_SCRIPT_URL=${PUBLIC_UMAMI_SCRIPT_URL}

RUN bun run build

# Production runner
FROM base AS runner

# Install dumb-init for proper signal handling
RUN apk add --no-cache dumb-init nodejs

ENV NODE_ENV=production
ENV HOST=0.0.0.0
ENV PORT=4400

WORKDIR /app

# Copy built application and node_modules
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

# Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S astro -u 1001 && \
    chown -R astro:nodejs /app

USER astro

EXPOSE 4400

# Use dumb-init to handle signals properly
ENTRYPOINT ["dumb-init", "--"]

# Start Astro SSR server with Bun
CMD ["bun", "run", "./dist/server/entry.mjs"]

