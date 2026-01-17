<?php
namespace App\Repositories\Order;

use App\Repositories\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    public function getData($request);

    public function insertData($data);
    static public function getCodeOrder($orderId);

    public function getTotalBaseCostByOrderId($orderId);
    public function getTotalRetailCostByOrderId($orderId);
    public function updateById($data, $order);

}
