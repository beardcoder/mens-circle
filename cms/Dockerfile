# Payload CMS Dockerfile with Bun
FROM oven/bun:1-alpine AS base

WORKDIR /app

# Dependencies stage
FROM base AS deps

# Install native dependencies for sharp (image processing)
RUN apk add --no-cache \
    vips-dev \
    build-base \
    python3

COPY package.json bun.lock* ./
RUN bun install --frozen-lockfile

# Builder stage
FROM base AS builder

RUN apk add --no-cache \
    vips-dev \
    build-base \
    python3

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Build arguments
ARG DATABASE_URI
ARG PAYLOAD_SECRET
ARG PAYLOAD_PUBLIC_SERVER_URL

ENV DATABASE_URI=${DATABASE_URI}
ENV PAYLOAD_SECRET=${PAYLOAD_SECRET}
ENV PAYLOAD_PUBLIC_SERVER_URL=${PAYLOAD_PUBLIC_SERVER_URL}
ENV NODE_ENV=production

RUN bun run build

# Production runner
FROM base AS runner

# Install runtime dependencies
RUN apk add --no-cache \
    vips \
    dumb-init

ENV NODE_ENV=production
ENV PORT=3001

WORKDIR /app

# Copy built application
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

# Create data directory for database
RUN mkdir -p /app/data

# Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S payload -u 1001 && \
    chown -R payload:nodejs /app

USER payload

EXPOSE 3001

# Use dumb-init for proper signal handling
ENTRYPOINT ["dumb-init", "--"]

CMD ["bun", "run", "dist/server.js"]

