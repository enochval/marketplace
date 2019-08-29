<?php


namespace App\Utils;


trait Response
{
    public function success($msg = 'Operation successful')
    {
        return response()->json([
            'status' => true,
            'message' => $msg
        ], 200);
    }

    public function withData($payload = [])
    {
        return response()->json([
            'status' => true,
            'data' => $payload
        ], 200);
    }

    public function error($msg = 'Error Occurred', $code = 400)
    {
        $code = ($code > 0 && $code < 501 ) ? $code : 400;
        return response()->json([
            'status' => false,
            'error' => $msg
        ], $code);
    }

    public function validationErrors($payload = [])
    {
        return response()->json([
            'status' => false,
            'error' => $payload
        ], 422);
    }

}
