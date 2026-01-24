<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Auth\ConfirmOTPRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\StoreRequest;
use App\Http\Requests\User\UpdateStoreRequest;
use App\Mail\RegisterConfirmation;
use App\Mail\ResetPassword;
use App\Models\Package;
use App\Models\UserPackage;
use App\Repositories\UserRepository\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function store(StoreRequest $request)
    {
        Log::info('register' . json_encode($request->all()));

        try {
            $data = $request->validated();
            $existUser = $this->userRepository->findByField('email', $data['email']);
            if ($existUser->count() > 0) {
                Log::info('register' . json_encode($data['email']));
                Log::info('email exists: ' . json_encode($existUser));
                return $this->response422('Email đã tồn tại!');
            }
            $data['password'] = bcrypt($data['password']);
            $data['type'] = 2;
            $data['status'] = 3;
            $data['otp'] = rand(100000, 999999);
            if (!isset($data['name']) || !$data['name']) {
                $data['name'] = $data['email'];
            }
            Log::info('register' . json_encode($data));

            DB::beginTransaction();
            $user = $this->userRepository->create($data);
            Mail::to($user->email)->send(new RegisterConfirmation($user));
            DB::commit();
            return $this->response200([
                'data' => $user,
                'meta' => [],
                'mess' => 'Đăng ký thành công, vui lòng xác nhận OTP để đăng nhập!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response500($e->getMessage());
        }
    }

    public function confirmOTP(ConfirmOTPRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $this->userRepository->findByEmail($data['gmail']);
            if (!$user) {
                return $this->response404('Người dùng với email này không tồn tại!');
            }

            if ($user->otp != $data['otp']) {
                return $this->response400('OTP không đúng!');
            }
            if ($user->status == 1 ) {
                return $this->response400('Tài khoản đã được xác nhận!');
            }
            $user->email_verified_at = now();
            $user->status = 1;
            $user->otp = null; // Clear the OTP after successful confirmation
            $this->assignTrialPackage($user);
            $user->save();
            return $this->response200([
                'data' => $user,
                'meta' => [],
                'mess' => 'Xác nhận OTP thành công!'
            ]);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $this->userRepository->findByEmail($data['email']);
            if (!$user) {
                return $this->response404('Email không tồn tại!');
            }

            if (!Auth::attempt($data)) {
                return $this->response400('Email hoặc mật khẩu không đúng!');
            }
            if ($user->remaining_days < 1 && $user->type == 2) {
                return $this->response400('Tài khoản của quý khách đã hết hạn sử dụng, vui lòng liên hệ với người quản trị hệ thống để gia hạn');
            }
            auth()->user()->update([
                'last_login_at' => Carbon::now()->toDateString(),
                'last_login_ip' => $request->getClientIp(),
            ]);
            auth()->user()->tokens()->delete();
            $token = Auth::attempt($data);
            $message = '';
            $check_remaining_day = $user->remaining_days;
            if ($check_remaining_day <= 5 && $user->type == 2 )
            {
                $message = "Số ngày sử dụng của quý khách đã sắp hết, vui lòng gia hạn sớm để tránh gián đoạn dịch vụ!";
            }
            return $this->response200($this->userRepository->responseWithToken($token), $message);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function updateStoreName(UpdateStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = auth()->user();
            $user = $this->userRepository->find($user->id);
            if (!$user || $user->type == 3) {
                return $this->response404();
            }
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $user->update($data);
            DB::commit();
            return $this->response200($user);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->response500($e->getMessage());
        }

    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $this->userRepository->findByEmail($data['email']);

            if (!$user) {
                return $this->response404('Email không tồn tại!');
            }

            // Step 1: If only email is provided, generate and send OTP
            if (!isset($data['otp']) && !isset($data['new_password'])) {
                $user->otp = rand(100000, 999999);
                $user->save();

                Mail::to($user->email)->send(new ResetPassword($user));

                return $this->response200([
                    'data' => null,
                    'meta' => [],
                    'mess' => 'Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra email để đặt lại mật khẩu!'
                ]);
            }

            // Step 2: If OTP and new password are provided, verify OTP and reset password
            if (isset($data['otp']) && isset($data['new_password'])) {
                if ($user->otp != $data['otp']) {
                    return $this->response400('Mã OTP không đúng!');
                }

                $user->password = bcrypt($data['new_password']);
                $user->otp = null; // Clear the OTP after successful password reset
                $user->save();

                return $this->response200([
                    'data' => null,
                    'meta' => [],
                    'mess' => 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập với mật khẩu mới!'
                ]);
            }

            return $this->response400('Yêu cầu đặt lại mật khẩu không hợp lệ!');
        } catch (\Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());
            return $this->response500($e->getMessage());
        }
    }

    public function refresh()
    {
        try {
            $newToken = auth()->refresh();
            return $this->response200($this->userRepository->responseWithToken($newToken), 'Token đã được làm mới thành công!');
        } catch (\Exception $e) {
            Log::error('Refresh token error: ' . $e->getMessage());
            return $this->response401('Token không hợp lệ hoặc đã hết hạn!');
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return $this->response200([], 'Đăng xuất thành công!');
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return $this->response500($e->getMessage());
        }
    }

    private function assignTrialPackage($user)
    {
        $trialPackage = Package::trialPackage();
        Log::info(json_encode($trialPackage));
        if ($user && $trialPackage) {
            UserPackage::query()->create([
                'user_id' => $user->id,
                'package_id' => $trialPackage->id,
                'days' => $trialPackage->days,
            ]);
            $user->remaining_days = $trialPackage->days;
        }
    }
}
