<?php
namespace App\Repositories\OrderDetail;

use App\Repositories\RepositoryInterface;

interface OrderDetailRepositoryInterface extends RepositoryInterface
{
    public function findByField($field, $value);
    public function getDataPurchase($orderDetails, $order);
    public function getDataSales($orderDetails, $order);
    public function getDataUpdate($orderDetail, $order);
    public function getDataUpdateSales($orderDetail, $order);
    public function cancelDataUpdate($orderDetail, $order);
}
