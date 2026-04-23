<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;

class AttendanceController extends Controller
{
    public function index()
    {
        if (!session('user_id')) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập');
        }
        $user = \App\Models\User::find(session('user_id'));

        // Check quyền
        if ($user->type == 1) {
            return redirect('/dashboard');
        }
        $user = $this->getUser();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        return view('attendance', compact('attendance'));
    }

    public function dashboard()
    {
        if (!session('user_id')) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập');
        }

        $user = \App\Models\User::find(session('user_id'));

        // Check quyền
        if (!$user || $user->type != 1) {
            return redirect('/')->with('error', 'Bạn không có quyền truy cập Dashboard');
        }

        $attendances = Attendance::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $users = User::with('company')->where('type', '!=', 1)->get();

        $companies = Company::all();

        $totalUsers = \App\Models\User::count();
        $totalCompanies = \App\Models\Company::count();

        $todayCheckin = \App\Models\Attendance::whereDate('work_date', today())
            ->whereNotNull('check_in_time')
            ->count();

        $notCheckin = $totalUsers - $todayCheckin;

        return view('dashboard', compact('attendances', 'users', 'companies', 'totalUsers', 'totalCompanies', 'todayCheckin', 'notCheckin'));
    }

    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function saveImage($imageBase64)
    {
        $image = str_replace('data:image/png;base64,', '', $imageBase64);
        $fileName = 'attendance/' . uniqid() . '.png';

        \Storage::disk('public')->put($fileName, base64_decode($image));

        return $fileName;
    }

    public function checkIn(Request $request)
    {
        $user = $this->getUser();

        if (!$user) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập');
        }

        $company = $user->company;

        $distance = $this->distance(
            $request->lat,
            $request->lng,
            $company->lat,
            $company->lng
        );

        if ($distance > $company->radius) {
            return back()->with('error', 'Bạn đang ngoài phạm vi công ty');
        }

        $image = $this->saveImage($request->image);

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => today()
            ]
        );

        if ($attendance->check_in_time) {
            return back()->with('error', 'Đã check-in rồi');
        }

        $attendance->update([
            'check_in_time' => now(),
            'check_in_lat' => $request->lat,
            'check_in_lng' => $request->lng,
            'check_in_image' => $image,
            'check_in_distance' => $distance
        ]);

        return back()->with('success', 'Check-in thành công');
    }

    public function checkOut(Request $request)
    {
        $user = $this->getUser();

        if (!$user) {
            return redirect('/login')->with('error', 'Vui lòng đăng nhập');
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return back()->with('error', 'Bạn chưa check-in');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'Đã check-out rồi');
        }

        $company = $user->company;

        $distance = $this->distance(
            $request->lat,
            $request->lng,
            $company->lat,
            $company->lng
        );

        if ($distance > $company->radius) {
            return back()->with('error', 'Bạn đang ngoài phạm vi công ty');
        }

        $image = $this->saveImage($request->image);

        $attendance->update([
            'check_out_time' => now(),
            'check_out_lat' => $request->lat,
            'check_out_lng' => $request->lng,
            'check_out_image' => $image,
            'check_out_distance' => $distance
        ]);

        return back()->with('success', 'Check-out thành công');
    }
    private function getUser()
    {
        return \App\Models\User::find(session('user_id'));
    }

    public function history(Request $request)
    {
        $user = \App\Models\User::find(session('user_id'));

        if (!$user) {
            return redirect('/login');
        }

        $month = $request->month ?? now()->format('Y-m');

        $attendances = \App\Models\Attendance::where('user_id', $user->id)
            ->whereYear('work_date', date('Y', strtotime($month)))
            ->whereMonth('work_date', date('m', strtotime($month)))
            ->orderBy('work_date', 'desc')
            ->get();

        return view('history', compact('attendances', 'month'));
    }

    public function attendance_detail(Request $request)
    {
        $query = Attendance::with('user.company');

        // lọc tháng
        if ($request->month) {
            $query->whereMonth('work_date', date('m', strtotime($request->month)))
                ->whereYear('work_date', date('Y', strtotime($request->month)));
        }

        // lọc công ty
        if ($request->company_id) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // lọc nhân viên
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('work_date', 'desc')->get();

        return view('attendance_detail', [
            'attendances' => $attendances,
            'users' => User::all(),
            'companies' => Company::all(),
            'filters' => $request->all()
        ]);
    }
}
