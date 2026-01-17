<?php

namespace App\Repositories\CustomerDebtHistory;

use App\Repositories\RepositoryInterface;

interface CustomerDebtHistoryInterface extends RepositoryInterface
{
    public function getCustomerDebt($request, $id);
}
