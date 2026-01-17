<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;


class OrderExport implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */


    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function view(): view
    {
        return view('export.exportOrder', $this->data);
    }


}
