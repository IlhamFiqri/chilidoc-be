<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helper\ResponseHelper;
use App\Models\User;
use \Auth;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        $user = Auth::user();
        return ResponseHelper::sendResponse($user, '', 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
	        'email' => 'required',
	        'password' => 'required',
	    ], [
            'email.required' => 'Kolom email harus diisi!',
            'password.required' => 'Kolom password harus diisi!',
        ]);
	    if ($validator->fails()) {
            return ResponseHelper::sendError($validator->errors()->first(), 422);
	    }
	    $data = $validator->validated();
        $user = User::where('email', $data['email'])->first();
        if(!$user || !Hash::check($data['password'], $user->password)) {
            return ResponseHelper::sendError('Email atau password anda salah!', 401);
        }
        $token = $user->createToken('TOKENAPP')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token,
        ];
        return ResponseHelper::sendResponse($response, 'Login Berhasil!', 200);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
	        'name' => 'required',
	        'email' => 'required|unique:users,email',
	        'password' => 'required|confirmed',
	    ], [
            'name.required' => 'Kolom nama harus diisi!',
            'email.required' => 'Kolom email harus diisi!',
            'email.unique' => 'Email yang diisi telah digunakan pengguna lain!',
            'password.required' => 'Kolom password harus diisi!',
            'password.confirmed' => 'Konfirmasi password harus sama!',
        ]);
	    if ($validator->fails()) {
            return ResponseHelper::sendError($validator->errors()->all()[0], 422);
	    }
	    $data = $validator->validated();
        try{
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();
            $token = $user->createToken('TOKENAPP')->plainTextToken;
            $response = [
                'user' => $user,
                'token' => $token,
            ];
            return ResponseHelper::sendResponse($response, 'Pendaftaran Berhasil!', 201);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
        
    }

    public function logout(Request $request) {
        try{
            Auth::user()->tokens()->delete();
            return ResponseHelper::sendResponse(null, 'Logout Berhasil!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }
    
    public function updateProfile(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'gender' => 'required',
            'birth_date' => 'required|date',
            'phone' => 'required',
            'address' => 'required',
            'picture' => 'nullable',
        ], [
            'name.required' => 'Kolom nama harus diisi!',
            'gender.required' => 'Kolom jenis kelamin harus diisi!',
            'birth_date.required' => 'Kolom tanggal lahir harus diisi!',
            'birth_date.date' => 'Kolom tanggal lahir harus format tanggal!',
            'phone.required' => 'Kolom telepon harus diisi!',
            'address.required' => 'Kolom alamat harus diisi!',
            'picture.image' => 'Kolom foto profil harus berupa gambar!',
            'picture.max' => 'Gambar harus < 5MB!',
        ]);
        $data = $validator->validated();

        if ($request->hasFile('picture')){
            $disk = Storage::disk('local_image');

            $user = User::find(Auth::id());
            $oldPath = str_replace('/images', '', $user['picture']);
            if ($disk->exists($oldPath)){
                $disk->delete($oldPath);
            }

            $file = $disk->put('/profile', $request->file('picture'));
            if($file) {
                $path = '/images' .'/' . $file;
                $data['picture'] = $path;
            }
        } 

        try{
            User::find(Auth::id())->update($data);
            $user = User::find(Auth::id());
            return ResponseHelper::sendResponse($user, 'Update profile!', 200);
        }catch(\Exception $ex){
           return ResponseHelper::throw($ex);
        }
    }
}
