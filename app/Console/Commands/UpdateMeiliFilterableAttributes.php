<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MeiliSearch\Client;

class UpdateMeiliFilterableAttributes extends Command
{
    protected $signature = 'meili:update-filterable';
    protected $description = 'Cập nhật filterable attributes cho Meilisearch index';

    public function handle()
    {
        $host = config('scout.meilisearch.host');
        $key = config('scout.meilisearch.key');

        $this->info("Kết nối tới Meilisearch tại: $host");

        $client = new Client($host, $key);

        // Đặt tên index trùng tên với index trong model Product
        $index = $client->index('products');

        $attributes = ['user_id']; // các field cần filter

        $index->updateFilterableAttributes($attributes);

        $this->info('Đã cập nhật filterable attributes: ' . implode(', ', $attributes));
    }
}
