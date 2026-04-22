<?php

namespace App\Models;

use App\Enum\ProductStatus;
use App\Traits\DynamicPagination;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory ,DynamicPagination , HasUuids , SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'stock_quantity',
        'low_stock_threshold',
        'status',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
        'status'              => ProductStatus::class,
    ];
    protected $hidden = ['deleted_at'];

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
}
