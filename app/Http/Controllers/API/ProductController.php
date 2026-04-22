<?php

namespace App\Http\Controllers\API;

use App\Events\StockThresholdReached;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\AdjustStockRequest;
use App\Http\Requests\API\StoreProductRequest;
use App\Http\Requests\API\UpdateProductRequest;
use App\Http\Resources\API\ProductCollection;
use App\Http\Resources\API\ProductResource;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(private readonly ProductRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);
        $page    = $request->integer('page', 1);

        $products = Cache::tags(['products'])
        ->remember(
            "products.page.{$page}.per_page.{$perPage}",
            300,
            fn() => $this->repository->getAll($perPage)
        );

        return $this->respondWithCollection(new ProductCollection($products));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->repository->create($request->validated());
        $this->clearProductCache();

      return $this->respondWithCreated(new ProductResource($product), 'Product created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = $this->repository->findById($id);

        if (!$product) {
            return $this->errorNotFound('Product not found');
        }

        return $this->respondWithRetrieved(new ProductResource($product), 'Product retrieved successfully');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = $this->repository->findById($id);

        if (!$product) {
            return $this->errorNotFound('Product not found');
        }

        $updated = $this->repository->update($product, $request->validated());
        $this->clearProductCache();
        return $this->respondWithUpdated(new ProductResource($updated), 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = $this->repository->findById($id);

        if (!$product) {
           return $this->errorNotFound('Product not found');
        }

        $this->repository->delete($product);
        $this->clearProductCache();

        return $this->respondWithDeleted('Product deleted successfully');
    }
    public function adjustStock(AdjustStockRequest $request, string $id): JsonResponse
    {
        $product = $this->repository->findById($id);

        if (!$product) {
           return $this->errorNotFound('Product not found');
        }

        $newQuantity = $product->stock_quantity + $request->integer('quantity');

        if ($newQuantity < 0) {
           return $this->errorDatabase("Insufficient stock");
        }

        $updated = $this->repository->adjustStock(
            $product,
            $request->integer('quantity')
        );


        if ($updated->isLowStock()) {
            StockThresholdReached::dispatch($updated);
        }

        $this->clearProductCache();

        return $this->respondWithUpdated(new ProductResource($updated), 'Product stock adjusted successfully');
    }
    public function lowStock(Request $request)
    {

        $perPage  = $request->integer('per_page', 15);
        $page     = $request->integer('page', 1);

        $products = Cache::tags(['products', 'low_stock'])
        ->remember(
            "products.low_stock.page.{$page}.per_page.{$perPage}",
            300,
            fn() => $this->repository->getLowStock($perPage)
        );

        return $this->respondWithCollection(new ProductCollection($products));
    }
    private function clearProductCache(): void
    {

        Cache::tags(['products'])->flush();
    }

}
