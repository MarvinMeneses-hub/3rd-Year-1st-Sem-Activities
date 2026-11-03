# Lab 6 Integration Notes — StockPilot Inventory Management

This update extends the existing Laravel 12 Lab 5 project. It is not a replacement project.

## Included Lab 6 work

- Inventory transaction history with product/type/date filters and pagination.
- Stock-in, stock-out, and signed stock-adjustment recording.
- Eloquent observer updates product stock when a transaction is created.
- Database-backed low-stock/out-of-stock notifications for the user who records the transaction.
- Product-to-transaction and user-to-transaction relationships.
- Product stock helpers and low-stock/out-of-stock query scopes.
- Transaction detail and reporting pages.
- Authenticated JSON inventory summary endpoint:
  `GET /api/inventory/summary`
- Transaction history page refreshes its summary cards every 30 seconds.
- Product quantity changes made through the existing Edit Product form are recorded as adjustment transactions.
- Products with transaction history are protected from deletion so their audit history is retained.
- Feature tests for stock changes, insufficient stock, adjustments, product edits, deletion protection, authentication, and the summary endpoint.

## Apply the update safely

1. Back up your existing project folder and database first.
2. Extract this ZIP into `C:\\xampp1\\htdocs` so its `inventory-management-main` folder merges with your existing project folder. If Windows extracts it as a separate folder, copy the contents of that extracted folder into `C:\\xampp1\\htdocs\\inventory-management-main` and allow files to merge/overwrite.
3. Keep your existing `.env`, `vendor`, and `node_modules` folders. They are intentionally not included in this ZIP.
4. Open CMD in `C:\xampp1\htdocs\inventory-management-main`.
5. Run:

```cmd
php artisan optimize:clear
php artisan migrate
php artisan route:list --path=transactions
php artisan route:list --name=api.inventory.summary
```

Do **not** run `php artisan migrate:fresh`; it would drop existing tables and data.

## Manual verification

1. Log in to the existing application.
2. Open **Transactions** from the navigation.
3. Record a Stock In transaction and confirm the product quantity increases.
4. Record a Stock Out transaction and confirm the product quantity decreases.
5. Try to stock out more than the available quantity; the application should reject it without changing stock.
6. Record an Adjustment: positive values add stock and negative values remove stock.
7. Edit an existing product's quantity from the Products page. The difference should appear in transaction history as an Adjustment.
8. Open a transaction's details and the Transaction Reports page.
9. Set a product's stock at or below its reorder level through a transaction. A database notification should be created for the user who recorded it.
10. Leave the Transaction History page open and confirm its summary cards refresh every 30 seconds.

## Important behavior

- The quantity on a new product is its initial opening stock.
- After product creation, stock changes should be recorded as transactions.
- Historical transactions cannot be edited or deleted.
- Products with transaction history cannot be deleted, protecting the audit trail.
- Stock adjustments use a signed quantity: `+5` adds five units; `-5` removes five units.
- The application continues to use the existing authentication and database connection from your local `.env`.

## Verification performed while preparing this package

- PHP syntax check passed for 52 first-party PHP files.
- Laravel route registration check passed for the transaction routes and inventory summary endpoint.
- Full automated tests and database migrations could not be completed in this preparation environment because the PHP runtime here lacks the DOM, mbstring, and PDO SQLite extensions. Run the commands above in your XAMPP environment and report any error output before proceeding.
