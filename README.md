# Spare Parts Shop (UAE)

Laravel inventory and billing for a spare-parts counter: products, stock, garage accounts, UAE VAT tax invoices, credit/installment collections, same-day void, and tax credit notes.

## Requirements

- PHP 8.4+ with `pdo_sqlite` (or MySQL / Postgres)
- Composer
- Extensions: `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` (recommended), `gd` or `imagick` (DomPDF)

## Setup

```bash
composer install
cp .env.example .env   # already present in this repo; rotate APP_KEY in production
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

## Trial login

| User | Email | Password | Role |
| --- | --- | --- | --- |
| Admin | admin@shop.local | password | settings + same-day void |
| Staff | staff@shop.local | password | bill, stock, collections |

The public Render trial uses SQLite that resets when the free instance sleeps. Add products, receive stock, then bill.

Put the shop **TRN**, address, and VAT % under **Settings** (admin). Default VAT is **5%**. Currency is **AED** (stored as fils).

## Deploy (Render)

This repo includes a `Dockerfile` and `render.yaml`. After pushing to GitHub, open:

https://dashboard.render.com/blueprint/new?repo=YOUR_GITHUB_REPO_URL

or create a Docker web service from the repo. Bind to `0.0.0.0:$PORT` (handled by the entrypoint).

For MySQL, set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`, then migrate again.

## How staff use it

1. **Products** — name, SKU, MRP (VAT exclusive), optional VAT override, minimum quantity.
2. **Stock** — receive goods when they arrive. Red = at or below minimum. **Low stock → Download CSV** for purchase orders.
3. **Garages** — default payment: ready cash, credit (days), or installment (count). Edit anytime; only new bills pick up the change.
4. **Bill** (always first in the top bar) — choose garage to fill payment fields, then recorrect if needed. Confirm issues a sequential **Tax Invoice**, downloads as PDF, and **reduces stock**.
5. **Collections** — credit due dates and installments (default due on the **3rd** of each month).
6. **Dashboard** — low stock and due/overdue money.
7. Wrong bill **same calendar day** (Asia/Dubai): admin **Void**. Later: **Tax Credit Note** (stock back, dues reduced).

Walk-in sales (no garage) are **ready cash** only.

## Tests

```bash
php artisan test
```
