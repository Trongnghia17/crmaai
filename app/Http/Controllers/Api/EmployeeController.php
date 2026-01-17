<?php

namespace App\Http\Controllers\Api;



use App\Http\Requests\Employee\CreateRequest;
use App\Http\Requests\Employee\UpdateRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Employee;
use App\Repositories\Employee\EmployeeRepositoriesInterface;
use App\Repositories\UserRepository\UserRepositoryInterface;
use App\Repositories\Employee\EmployeeRepository;
use App\Repositories\Pagination;;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{

    protected $employeeRepository;
    protected $userRepository;

    public function __construct(
        EmployeeRepositoriesInterface $employeeRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->employeeRepository = $employeeRepository;
        $this->userRepository = $userRepository;
    }



    public function create(CreateRequest $request)
    {
        Log::info('Create employee' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $data['type'] = 3;
            $data['is_active'] = 1;
            $data['status'] = 3;
            $employee = $this->userRepository->create($data);
            if (!$employee) {
                return $this->response422('Tạo nhân viên thất bại');
            }
            Employee::create([
                'user_id' => $employee->id,
                'manager_id' => auth()->id(),
            ]);
            DB::commit();
            return $this->response200($employee);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            $data = $this->employeeRepository->getEmployee($request);
            $paginate = new Pagination($data);
            return $this->response200(
                EmployeeResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $employee = $this->userRepository->find($id);
            if (!$employee) {
                return $this->response422('Nhân viên không tồn tại');
            }
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            Log::info('Update employee' . json_encode($data));;
            $employee->update($data);
            DB::commit();
            return $this->response200($employee);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response500($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $employee = $this->userRepository->find($id);
            if (!$employee) {
                return $this->response404('Nhân viên không tồn tại');
            }
            if ($employee->type == 1) {
                return $this->response422('Không thể xóa admin');
            }
            $employee->is_active = 0;
            $employee->save();
            DB::commit();
            return $this->response200('xóa nhân viên thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response500($e->getMessage());
        }
    }
}
