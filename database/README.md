# StockTangent Laravel 12 schema

Includes 10 new migrations, 10 new models, and replacement `User.php`.

## Sample data

Run `php artisan migrate:fresh --seed` to rebuild the database and load sample data. This deletes existing data.

The seed creates exactly two verified users, both with password `password`:

- `admin@stocktangent.com`
- `member@stocktangent.com`

It also creates one organization, 20 products, three stores, three inventories (one organization-owned and one owned by each user), two organization memberships, six inventory memberships, 60 store prices, 60 inventory items, six transactions, and 120 transaction items. Inventory quantities equal purchases minus usage. Inventory owners have `admin` permission and the other user has `editor` permission; there is no global user role column.

Factories are available for every application model. `Inventory::factory()->forOrganization()` creates an organization-owned inventory; the default creates a user-owned inventory. The seed explicitly reuses the two accounts rather than allowing factories to create extra users.

New products receive unique UUID QR code values from the product factory. To fill missing QR code values on existing products without recreating sample data, run `php artisan db:seed --class=ProductQrcodeSeeder`. Existing QR code values are preserved. This seeder also runs as part of the normal database seed.

Users use `firstname` and `lastname`. Organizations use a single required `name` field, and their factory generates company names. The organization name migration combines existing first and last names before removing those columns. Both factories populate `address_line1`, `address_line2`, `city`, `state`, and `postal_code`. State columns on users, organizations, and stores are `CHAR(2)` and their factories generate uppercase US postal abbreviations.

Laravel's sessions, password reset tokens, cache, locks, jobs, job batches, and failed jobs tables are populated by the framework at runtime and are intentionally left empty by the seed.

## Setup
1. Back up your current `app/Models/User.php` and migrations.
2. Copy the files from `app/Models/` into your project's `app/Models/`.
3. Copy the migration PHP files into `database/migrations/`.
4. Do NOT also run `make:model -m` for the same tables, or you will have duplicate create-table migrations. If you already generated them, replace their contents and use chronological timestamps matching this package.
5. The users migration includes `softDeletes()` for clean databases. For existing databases that lack this column, create an additive users migration. See `database/migrations/README_USERS.txt`.
6. Run `php artisan migrate:status` then `php artisan migrate`.

## Important schema notes
- Products have an optional, globally unique `qrcode` string (up to 255 characters) for QR code values. The product factory generates UUID values for this field.
- MySQL 8.0.16+ is required to enforce the owner CHECK constraint. The migration uses `DB::statement()` after table creation.
- The diagram references `organization_user` without showing its columns; this package uses `id, organization_id, user_id, created_at, updated_at`.
- Pivot tables have models for convenience but ordinary `belongsToMany()` relations use Laravel's default pivot objects.
- Global unique SKU/barcode means two unrelated organizations cannot reuse the same SKU. For multi-tenant isolation, consider inventory-scoped product catalogs instead.
- Product/store pivot records represent current price, not historical price snapshots. Transactions store `unit_price` for history.
- Transaction rows should be written together with inventory quantity changes inside a DB transaction; application-level validation must ensure transaction item belongs to transaction's inventory and product matches inventory item.
- Membership authorization must be enforced with policies; database enum values alone do not grant/deny access.
- Soft-deleted inventory items still reserve `(inventory_id, product_id)` unique key. Restore existing row instead of inserting duplicate.
- `cascadeOnDelete` on membership/price pivots only runs when parent rows are hard-deleted, not soft-deleted.
