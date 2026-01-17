<?php
namespace App\Repositories\Customer;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    public function getCustomer($request);
    public function revenueByOrderNull(Request $request);
}
