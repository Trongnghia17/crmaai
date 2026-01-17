<?php

namespace App\Repositories\Inventory;

use App\Repositories\BaseRepository;

class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\Inventory::class;
    }

    public function getData($request)
    {
        return [
            "type" => $request->get('type', 1),
            'user_id' => auth()->id(),
            'status' => $request->get('status', 1),
            'code' => $this->getCD()
        ];
    }
    /**
     * @inheritDoc
     */
    public function getCD()
    {
        $lastRecord = $this->model->latest()->first();
        $id = 1;
        if ($lastRecord) {
            $id = $lastRecord->id + 1;
        }
        return "IN" . str_pad("{$id}", 5, '0', STR_PAD_LEFT);

    }

    /**
     * @inheritDoc
     */
    public function getList($request)
    {
        $query = $this->model
            ->with('user')
            ->where('user_id', auth()->id());
        build_query_sort_field($query, $request->get('sort', 'updated_at'));

        return $query
            ->paginate(
                $request->get('per_page', 20)
            );
    }
}
