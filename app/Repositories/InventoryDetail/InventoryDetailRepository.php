<?php

namespace App\Repositories\InventoryDetail;

use App\Models\InventoryDetail;
use App\Models\Product;
use App\Models\ProductStorage;
use App\Repositories\BaseRepository;

class InventoryDetailRepository extends BaseRepository implements InventoryDetailRepositoryInterface
{
    public function getModel()
    {
        return InventoryDetail::class;
    }

    public function getData($request, $inventoryId)
    {
        $status = $request->status;
        return collect($request->inventories_detail ?? [])->map(function ($item) use ($inventoryId, $status) {
            $inventoriesDetail = new InventoryDetail($item);

            $product = Product::query()->find($inventoriesDetail->product_id);

            if ($status) {
                $product->available = $inventoriesDetail->quantity_reality;
                $product->temporality += $inventoriesDetail->quantity_reality - $inventoriesDetail->quantity_current;
                $product->save();

                ProductStorage::query()->create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 4,
                    'quantity' => $product->available,
                    'quantity_change' => $inventoriesDetail->quantity_reality - $inventoriesDetail->quantity_current,
                ]);
            }

            $inventoriesDetail->product_id = $product->id;
            $inventoriesDetail->inventory_id = $inventoryId;

            return $inventoriesDetail;
        });
    }

    public function createOrderUpdate($dataInput, $inventoryId)
    {
        $dataInputDetail = $dataInput['inventories_detail'];
        foreach ($dataInputDetail ?? [] as $detail) {
            if (isset($detail['id'])) {
                if (isset($detail['is_delete']) && $detail['is_delete']) {
                    $this->model->whereKey($detail['id'])->delete();
                } elseif (isset($detail['is_delete']) && !$detail['is_delete']) {
                    $detailId = $detail['id'];
                    unset($detail['id']);
                    unset($detail['is_delete']);
                    $this->update($detailId, $detail);

                    $product = Product::query()->find($detail['product_id']);
                    if ($dataInput['status']) {
                        $product->available = $detail['quantity_reality'];
                        $product->temporality += $detail['quantity_reality'] - $detail['quantity_current'];
                        $product->save();

                        ProductStorage::query()->create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'type' => 4,
                            'quantity' => $product->available,
                            'quantity_change' => $detail['quantity_reality'] - $detail['quantity_current'],
                        ]);
                    }
                }
            } else {
                if (isset($detail['is_delete'])) {
                    unset($detail['is_delete']);
                }

                $product = Product::query()->find($detail['product_id']);
                if ($dataInput['status']) {
                    $product->available = $detail['quantity_reality'];
                    $product->temporality += $detail['quantity_reality'] - $detail['quantity_current'];
                    $product->save();

                    ProductStorage::query()->create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 4,
                        'quantity' => $product->available,
                        'quantity_change' => $detail['quantity_reality'] - $detail['quantity_current'],
                    ]);
                }

                $detail['inventory_id'] = $inventoryId;
                $detail['product_id'] = $product->product_id;
                $this->create($detail);
            }
        }
    }
}
