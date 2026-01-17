<?php
/**
 * Created by PhpStorm.
 * User: datcx
 * Date: 2020-07-30
 * Time: 14:18
 */

namespace App\Repositories;



use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class Pagination
 *
 * @package App\Repositories
 */
class Pagination
{
    const PAGINATION = 20;
    /**
     * @var array
     */
    protected $items = [];
    /**
     * @var array
     */
    protected $total = 0;
    /**
     * @var int
     */
    protected $size = 0;


    /**
     * Pagination constructor.
     *
     * @param LengthAwarePaginator $paginator
     */
    public function __construct(LengthAwarePaginator $paginator)
    {
        $this->items = $paginator;
        $this->total = $paginator->total();
        $this->size = $paginator->perPage();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'total' => $this->total,
            'size'  => $this->size
        ];
    }
}
