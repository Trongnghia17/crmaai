<?php
namespace App\Repositories\Dashboard;

use App\Repositories\RepositoryInterface;


interface DashboardRepositoryInterface extends RepositoryInterface
{
    public function getDashboard($userId);

}
