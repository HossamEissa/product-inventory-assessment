<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        $existing = Product::withTrashed()
            ->where('sku', $data['sku'])
            ->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update($data);
            return $existing->fresh();
        }

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function adjustStock(Product $product, int $quantity): Product
    {
        $product->increment('stock_quantity', $quantity);
        return $product->fresh();
    }

    public function getLowStock(int $perPage = 15): LengthAwarePaginator
    {
        return Product::lowStock()
            ->orderBy('stock_quantity', 'asc')
            ->paginate($perPage);
    }
}
