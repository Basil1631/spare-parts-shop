FROM node:22-alpine
RUN apk add --no-cache libc6-compat openssl
WORKDIR /app
COPY erp/package.json erp/package-lock.json ./
RUN npm ci
COPY erp/ ./
ENV NEXT_TELEMETRY_DISABLED=1
ENV DATABASE_URL="postgresql://build:build@127.0.0.1:5432/partszone"
RUN mkdir -p /app/data && npx prisma generate && npx next build
EXPOSE 10000
CMD ["node", "scripts/boot.mjs"]
