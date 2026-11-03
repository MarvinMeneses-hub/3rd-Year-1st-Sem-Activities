<?php

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

function makeInventoryProduct(array $overrides = []): Product
{
    return Product::create(array_merge([
        'name' => 'Test Product',
        'description' => 'Inventory test item',
        'price' => 25,
        'unit_price' => 25,
        'quantity' => 10,
        'reorder_level' => 3,
    ], $overrides));
}

it('records stock-in transactions and increases product quantity', function () {
    $user = User::factory()->create();
    $product = makeInventoryProduct(['quantity' => 10]);

    $this->actingAs($user)->post(route('transactions.store'), [
        'product_id' => $product->id,
        'type' => 'stock_in',
        'quantity' => 5,
        'reference_number' => 'PO-001',
        'submission_token' => (string) Str::uuid(),
    ])->assertRedirect(route('transactions.index'));

    expect($product->fresh()->quantity)->toBe(15);
    expect(InventoryTransaction::count())->toBe(1);
});

it('records stock-out transactions and decreases product quantity', function () {
    $user = User::factory()->create();
    $product = makeInventoryProduct(['quantity' => 10]);

    $this->actingAs($user)->post(route('transactions.store'), [
        'product_id' => $product->id,
        'type' => 'stock_out',
        'quantity' => 4,
        'submission_token' => (string) Str::uuid(),
    ])->assertRedirect(route('transactions.index'));

    expect($product->fresh()->quantity)->toBe(6);
});

it('rejects stock-out transactions that would make inventory negative', function () {
    $user = User::factory()->create();
    $product = makeInventoryProduct(['quantity' => 2]);

    $this->actingAs($user)->from(route('transactions.create'))->post(route('transactions.store'), [
        'product_id' => $product->id,
        'type' => 'stock_out',
        'quantity' => 3,
        'submission_token' => (string) Str::uuid(),
    ])->assertSessionHasErrors('quantity');

    expect($product->fresh()->quantity)->toBe(2);
    expect(InventoryTransaction::count())->toBe(0);
});

it('applies signed stock adjustments', function () {
    $user = User::factory()->create();
    $product = makeInventoryProduct(['quantity' => 10]);

    $this->actingAs($user)->post(route('transactions.store'), [
        'product_id' => $product->id,
        'type' => 'adjustment',
        'quantity' => -3,
        'notes' => 'Physical count correction',
        'submission_token' => (string) Str::uuid(),
    ])->assertRedirect(route('transactions.index'));

    expect($product->fresh()->quantity)->toBe(7);
    expect(InventoryTransaction::first()->quantity)->toBe(-3);
});

it('requires authentication to view transaction history', function () {
    $this->get(route('transactions.index'))->assertRedirect(route('login'));
});

it('provides an authenticated inventory summary endpoint', function () {
    $user = User::factory()->create();
    makeInventoryProduct(['quantity' => 2, 'reorder_level' => 3]);

    $this->actingAs($user)
        ->getJson(route('api.inventory.summary'))
        ->assertOk()
        ->assertJsonStructure([
            'total_transactions',
            'stock_in_units',
            'stock_out_units',
            'total_products',
            'total_units',
            'low_stock_count',
            'out_of_stock_count',
        ])
        ->assertJsonPath('low_stock_count', 1);
});

it('logs quantity changes made from the product edit form as adjustments', function () {
    $user = User::factory()->create();
    $product = makeInventoryProduct(['quantity' => 10]);

    $this->actingAs($user)->put(route('products.update', $product), [
        'name' => $product->name,
        'sku' => null,
        'description' => $product->description,
        'category' => null,
        'supplier' => null,
        'quantity' => 14,
        'reorder_level' => 3,
        'price' => 25,
        'unit_price' => 25,
    ])->assertRedirect(route('products.index'));

    expect($product->fresh()->quantity)->toBe(14);
    expect(
        InventoryTransaction::where('type', 'adjustment')->first()?->quantity
    )->toBe(4);
});

it('prevents deletion of a product that has transaction history', function () {
    $user = User::factory()->create();
    $product = makeInventoryProduct();

    InventoryTransaction::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'type' => 'stock_in',
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('error');

    expect(Product::find($product->id))->not->toBeNull();
});
