<?php

namespace App\Repositories\InventoryDetail;

use App\Repositories\RepositoryInterface;

interface InventoryDetailRepositoryInterface extends RepositoryInterface
{
    public function getData($request, $inventoryId);
    public function createOrderUpdate($dataInput, $inventoryId);
}
