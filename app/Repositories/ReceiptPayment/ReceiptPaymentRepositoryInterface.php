<?php

namespace App\Repositories\ReceiptPayment;

use App\Repositories\RepositoryInterface;

interface ReceiptPaymentRepositoryInterface extends RepositoryInterface
{
    public function getPartnerGroupName(&$data);

    public function getPartnerName(&$data);

    public static function getCD($id, $prefix);

    public function receipt($request);

    public function payment($request);

    public function getAll();

    public static function getPaymentType($payment_type = 1);

}
