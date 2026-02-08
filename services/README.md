# Crutox Microservices

This folder contains Laravel-based microservices extracted from the Crutox monolith. The public API (paths and request/response) stays the same; the gateway in the monolith forwards to these services when the corresponding feature flag is enabled.

## Services

- **mining/** – Mining, balance, start_coin, claim_bonus, daily reward, social claim, mining:update-balances cron
- **task/** – Task start/claim/track, get_daily_tasks, tasks:daily-reset, tasks:distribute-rewards cron
- **gamification/** – Mystery Box, Booster, Ad Booster
- **kyc/** – KYC eligibility, submit, status, progress, Didit integration

## Architecture and progress

- Full architecture: [../docs/ARCHITECTURE_MICROSERVICES_PLAN.md](../docs/ARCHITECTURE_MICROSERVICES_PLAN.md)
- Implementation status and next steps: [../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md](../docs/MICROSERVICES_IMPLEMENTATION_TRACKER.md)

Each service is a separate Laravel app sharing the same MySQL database; the monolith acts as the API gateway and forwards requests when the service is enabled.
