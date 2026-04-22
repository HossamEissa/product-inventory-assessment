<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function validProductData(array $overrides = []): array
    {
        return array_merge([
            'sku'                 => 'SKU-TEST-001',
            'name'                => 'Test Product',
            'description'         => 'Test description',
            'price'               => 29.99,
            'stock_quantity'      => 50,
            'low_stock_threshold' => 10,
            'status'              => 'active',
        ], $overrides);
    }
}
