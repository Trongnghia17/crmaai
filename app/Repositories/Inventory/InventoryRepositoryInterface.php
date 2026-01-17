<?php

namespace App\Repositories\Inventory;

use App\Repositories\RepositoryInterface;

interface InventoryRepositoryInterface extends RepositoryInterface
{
    public function getData($request);

    public function getCD();

    public function getList($request);
}
