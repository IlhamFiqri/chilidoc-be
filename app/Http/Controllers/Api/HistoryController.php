<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helper\ResponseHelper;
use App\Helper\PredictHelper;
use App\Models\History;
use \Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HistoryController extends Controller
{
    public function index(Request $request) {
        try{
            $data = History::where('user_id', Auth::id())->get();
            return ResponseHelper::sendResponse($data, '', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }

    public function detail(Request $request,$id) {
        try{
            $data = History::find($id);
            return ResponseHelper::sendResponse($data, '', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
	        'image' => 'required|image|max:5240',
	    ], [
            'result.required' => 'Kolom hasil harus diisi!',
        ]);
	    if ($validator->fails()) {
            return ResponseHelper::sendError($validator->errors()->all()[0], 422);
	    }
	    $data = $validator->validated();
        $image = $request->file('image');
        $imagePath = $image->getPathname();
        $imageName = $image->getClientOriginalName();

        $client = new Client();

        $baseUrl = env('MODEL_REST_API');

        $response = $client->request('POST', $baseUrl.'/predict', [
            'multipart' => [
                [
                    'name'     => 'image',
                    'contents' => fopen($imagePath, 'r'),
                    'filename' => $imageName,
                ],
            ],
        ]);

        // Get the response body and decode the JSON
        $responseBody = $response->getBody()->getContents();
        $predict = json_decode($responseBody, true);
        if (!empty($predict)) {
            $desc = $predict['Desc'];
            $disease = $predict['Disease'];
            $prevention = $predict['Prevention'];
            $treatment = $predict['Treatment'];

            $disk = Storage::disk('local_image');
            $file = $disk->put('history', $request->file('image'));
            if($file) {
                $path = '/images/'.$file;
                $data['image'] = $path;
            }
            $history = new History;
            $history->user_id = Auth::id();
            $history->image = $path;
            $history->result = $desc;
            $history->disease = $disease;
            $history->prevention = $prevention;
            $history->treatment = $treatment;
            $history->save();
            return ResponseHelper::sendResponse($history, 'History Berhasil diBuat!', 200);

        } else {
            return ResponseHelper::throw('Error');
        }
        try{

        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }
}
