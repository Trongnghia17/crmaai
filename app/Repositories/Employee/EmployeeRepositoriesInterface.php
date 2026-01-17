<?php

namespace App\Repositories\Employee;

use App\Repositories\RepositoryInterface;

interface EmployeeRepositoriesInterface extends RepositoryInterface
{
    public function getEmployee($request);
}
