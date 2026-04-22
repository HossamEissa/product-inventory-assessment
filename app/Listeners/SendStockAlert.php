<?php

namespace App\Listeners;

use App\Events\StockThresholdReached;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendStockAlert
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StockThresholdReached $event): void
    {
        Log::warning('Low stock alert', [
            'product_id'      => $event->product->id,
            'product_name'    => $event->product->name,
            'stock_quantity'  => $event->product->stock_quantity,
            'threshold'       => $event->product->low_stock_threshold,
        ]);
    }
}
