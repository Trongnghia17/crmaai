<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        Company::create($request->all());
        return back()->with('success', 'Thêm thành công');
    }

    public function update(Request $request, $id)
    {
        Company::findOrFail($id)->update($request->all());
        return back()->with('success', 'Cập nhật thành công');
    }

    public function delete($id)
    {
        Company::findOrFail($id)->delete();
        return back()->with('success', 'Xóa thành công');
    }
}
