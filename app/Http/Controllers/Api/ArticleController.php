<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helper\ResponseHelper;
use App\Models\Article;
use \Auth;

class ArticleController extends Controller
{
    public function index(Request $request) {
        try{
            $data = Article::all();
            return ResponseHelper::sendResponse($data, 'Article Berhasil diBuat!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }

    public function detail(Request $request,$id) {
        try{
            $data = Article::find($id);
            return ResponseHelper::sendResponse($data, 'Article Berhasil diBuat!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }

    public function delete(Request $request,$id) {
        try{
            $data = Article::find($id)->delete();
            return ResponseHelper::sendResponse($data, 'Article Berhasil diHapus!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
	        'title' => 'required',
	        'content' => 'required',
	        'image' => 'required|image|max:5240',
	    ], [
            'title.required' => 'Kolom judul harus diisi!',
            'content.required' => 'Kolom konten harus diisi!',
            'image.required' => 'Kolom gambar harus diisi!',
            'image.image' => 'Kolom gambar harus berupa gambar!',
            'image.max' => 'Gambar harus < 5MB!',
        ]);
	    if ($validator->fails()) {
            return ResponseHelper::sendError($validator->errors()->all()[0], 422);
	    }
	    $data = $validator->validated();
        try{
            $disk = Storage::disk('local_image');
            $file = $disk->put('article', $request->file('image'));
            if($file) {
                $path = '/images/'.$file;
                $data['image'] = $path;
            }
            $article = new Article;
            $article->image = $data['image'];
            $article->title = $data['title'];
            $article->content = $data['content'];
            $article->save();
            return ResponseHelper::sendResponse($article, 'Article Berhasil diBuat!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }

    public function update(Request $request,$id) {
        $validator = Validator::make($request->all(), [
	        'title' => 'required',
	        'content' => 'required',
	        'image' => 'nullable|image|max:5240',
	    ], [
            'title.required' => 'Kolom judul harus diisi!',
            'content.required' => 'Kolom konten harus diisi!',
            'image.image' => 'Kolom gambar harus berupa gambar!',
            'image.max' => 'Gambar harus < 5MB!',
        ]);
	    if ($validator->fails()) {
            return ResponseHelper::sendError($validator->errors()->all()[0], 422);
	    }
	    $data = $validator->validated();
        try{
            $disk = Storage::disk('local_image');
            $file = $disk->put('article', $request->file('image'));
            if($file) {
                $path = '/images/'.$file;
                $data['image'] = $path;
            }
            $article = Article::where("id",$id)->update($data);
            return ResponseHelper::sendResponse($article, 'Article Berhasil diUbah!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }



}
