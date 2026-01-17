<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Dashboard\DashboardRepositoryInterface;
use App\Repositories\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    protected $dashboardRepository;
    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->response404('Không tìm thấy thông tin quản lý!');
            }
            $dashBoard = $this->dashboardRepository->getDashboard($user->id);
            return $this->response200($dashBoard);

        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

}
