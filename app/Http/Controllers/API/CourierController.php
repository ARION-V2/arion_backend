<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourierController extends Controller
{

    public function all(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');

            if ($id) {
                $partner = User::find($id);
                if ($partner) {
                    return ResponseFormatter::success($partner, "Get courier Success");
                } else {
                    return ResponseFormatter::error([
                        'message' => "Data courier tidak ditemukan"
                    ], "Get courier failed");
                }
            }
            $value = User::where('role', 'kurir');


            if($name){
                $value->where('name', 'like', '%' . $name . '%');
            }

            return ResponseFormatter::success($value->orderBy('created_at', 'desc')->get(), "Get Courier Success");
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Get Courier Failed");
        }
    }
    
    public function update(Request $request)
    {
        try {
            Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required',
                'username' => 'required|unique:user,username',
                'no_hp' => 'required',
                'date_of_birth'=>'required',
                'file' => 'image',
            ],);

            $result = User::updateOrCreate(
                [
                    'id' => $request->id,
                ],
                [
                    'name' => $request->name,
                    'password' =>  Hash::make($request->password),
                    'username' => $request->username,
                    'role' => 'kurir',
                    'no_hp' => $request->no_hp,
                    'date_of_birth' => $request->date_of_birth,
                ]
            );
            if ($request->file) {
                if ($request->file('file')) {
                    $file = $request->file->store('assets/user', 'public');

                    $result  = User::find($result->id);
                    $lastFile = $result->photo;
                    $result->photo = $file;
                    $result->update();

                    if ($lastFile) {
                        if (Storage::disk('public')->exists($lastFile)) {
                            Storage::disk('public')->delete($lastFile);
                        }
                    }
                }
            }
            return ResponseFormatter::success(
                $result,
                'Courier Add/Update Success',
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => "Update profil gagal",
                'error' => $error->getMessage(),
            ],  'Courier Add/Update Failed', 500);
        }
    }


    public function delete(Request $request)
    {

        try {
            Validator::make($request->all(), [
                'id' => 'required',
            ],);

            $result = User::where('id', $request->id)->delete();

            return ResponseFormatter::success(
                $result,
                'Courier Delete Success',
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => "Delete Courier Failed",
                'error' => $error,
            ],  'Delete Courier Failed', 500);
        }
    }
}
