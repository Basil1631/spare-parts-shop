FROM node:22-alpine
RUN apk add --no-cache libc6-compat openssl
WORKDIR /app
COPY erp/package.json erp/package-lock.json ./
RUN npm ci
COPY erp/ ./
ENV NEXT_TELEMETRY_DISABLED=1
ENV DATABASE_URL="file:/app/data/erp.db"
RUN mkdir -p /app/data && npx prisma generate && npx next build
EXPOSE 10000
CMD ["node", "scripts/boot.mjs"]
