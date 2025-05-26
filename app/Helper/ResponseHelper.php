<?php

namespace App\Helper;

use Illuminate\Http\Exceptions\HttpResponseException;

class ResponseHelper
{
    /**
     * Create a new class instance.
     */
    
    public static function throw($e, $message ="Something went wrong! Process not completed"){
        throw new HttpResponseException(response()->json(["message"=> $message], 500));
    }

    public static function sendResponse($result, $message, $code=200){
        $response=[
            'success' => true,
            'data'    => $result
        ];
        if(!empty($message)){
            $response['message'] =$message;
        }
        return response()->json($response, $code);
    }
    
    public static function sendError($message, $code){
        $response=[
            'success' => false,
            'message'    => $message
        ];
        if(!empty($message)){
            $response['message'] = $message;
        }
        return response()->json($response, $code);
    }
}