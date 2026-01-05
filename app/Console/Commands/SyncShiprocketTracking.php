<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ShiprocketService;
use Illuminate\Console\Command;

class SyncShiprocketTracking extends Command
{
    protected $signature = 'shiprocket:sync-tracking {--force : Run even for delivered/cancelled orders}';

    protected $description = 'Refresh Shiprocket tracking for live shipments';

    public function handle(ShiprocketService $shiprocket): int
    {
        $query = Order::query()
            ->whereNotNull('shiprocket_awb')
            ->whereNotNull('shiprocket_shipment_id');

        if (!$this->option('force')) {
            $query->whereNotIn('delivery_status', ['delivered', 'cancelled']);
        }

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->chunkById(50, function ($orders) use ($shiprocket, $bar) {
            foreach ($orders as $order) {
                $shiprocket->track($order);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Shiprocket tracking sync complete.');

        return Command::SUCCESS;
    }
}
