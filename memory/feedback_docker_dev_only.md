---
name: feedback_docker_dev_only
description: User is building a local development Docker setup, not production
metadata:
  type: feedback
---

This Docker setup is for local development only, not production.

**Why:** User explicitly said "I am just using this setup for development not production."

**How to apply:** 
- Mount source code as volumes (don't COPY everything into image)
- Keep composer install with dev dependencies (no --no-dev flag)
- Use npm run dev (Vite HMR) not npm run build
- No production optimizations (no --optimize-autoloader, no asset pre-building in Dockerfile)
- Dockerfile should be a lightweight dev base, not a production artifact
