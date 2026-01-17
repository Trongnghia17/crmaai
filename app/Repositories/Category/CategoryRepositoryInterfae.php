<?php

namespace App\Repositories\Category;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
interface CategoryRepositoryInterfae extends RepositoryInterface
{
    public function getCategory($request);

}
