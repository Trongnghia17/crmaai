<?php

namespace App\Repositories\Employee;

use App\Models\Category;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\Pagination;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoriesInterface
{

    public function getModel()
    {
        return User::class;
    }

    public function getEmployee($request)
    {
        $managerId = auth()->id();

        return Employee::with(['user', 'manager'])
            ->where('manager_id', $managerId)
            ->paginate(10);
    }

}
