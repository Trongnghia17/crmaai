<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param array|null $data
     * @param array $meta
     * @return JsonResponse
     */
    public function response200($data = [], $meta = [], $mess = 'Thao tác thành công!', $total = []): JsonResponse
    {
        header('Content-type: application/json');
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'success' => true,
            'status' => 200,
            'message' => $mess,
            'data' => $data,
            'meta' => $meta,
            'total' => $total
        ]);
        die;
    }

    /**
     * @param array|null $data
     * @param array $meta
     * @return JsonResponse
     */
    public function response204($data = [], $meta = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
        ], 204);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response401($msg = null): JsonResponse
    {
        $msg = $msg ?? trans('response.error_401');

        return response()->json([
            'message' => $msg,
        ], 401);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response400($msg = null): JsonResponse
    {
        $msg = $msg ?? trans('response.error_400');

        return response()->json([
            'message' => $msg,
        ], 400);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response403($msg = null): JsonResponse
    {
        $msg = $msg ?? trans('response.error_403');

        return response()->json([
            'message' => $msg,
        ], 403);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response500($msg = null): JsonResponse
    {
        Log::error($msg);
        return response()->json([
            'message' => 'Có lỗi, vui lòng thử lại.',
        ], 500);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response503($msg = null): JsonResponse
    {
        $msg = $msg ?? trans('response.error_503');

        return response()->json([
            'message' => $msg,
        ], 503);
    }



    /**
     * @param  null  $msg
     * @return JsonResponse
     */
    public function response422($msg = null): JsonResponse
    {
        return response()->json([
            'message' => $msg
        ], 422);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response404($msg = null): JsonResponse
    {
        return response()->json([
            'message' => $msg
        ], 404);
    }

    /**
     * @param null $msg
     *
     * @return JsonResponse
     */
    public function response410($msg = null): JsonResponse
    {
        return response()->json([
            'message' => $msg
        ], 410);
    }

}
