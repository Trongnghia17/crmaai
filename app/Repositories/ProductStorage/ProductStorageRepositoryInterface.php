<?php

namespace App\Repositories\ProductStorage;

use App\Repositories\RepositoryInterface;
interface ProductStorageRepositoryInterface extends RepositoryInterface
{
    public function getHistoryStorageByProductId($request);
    public function getTotalHistoryStorageByProductId($request);
    public function getStorageStart($request, $productId);
    public function getStorageEnd($request, $productId);
    public function getStorage($request, $type = 1);
}
