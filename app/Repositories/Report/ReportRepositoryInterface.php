<?php

namespace App\Repositories\Report;

use App\Repositories\RepositoryInterface;

interface ReportRepositoryInterface extends RepositoryInterface
{
    public function aggregate($orders);


}
