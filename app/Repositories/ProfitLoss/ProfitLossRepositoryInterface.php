<?php

namespace App\Repositories\ProfitLoss;
use App\Repositories\RepositoryInterface;

interface ProfitLossRepositoryInterface extends RepositoryInterface
{
    public function getReport($request);
    public function getReportToday();

}
