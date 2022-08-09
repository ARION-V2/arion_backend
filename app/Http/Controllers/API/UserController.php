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

class UserController extends Controller
{
    public function getMyUser(Request $request)
    {
        try {
            // NotificationManager::sendNotification("ini test", "ini test juga","eODOZzLZQYaE7-F35QDxLH:APA91bHyHfE8MdqtGk4V97Nd9UcMBj6igtVwvENFGJGGXL16J35iZOzeddkJzoJ4rF_h39zBdtfg_N38ftFLIhDBFwiZJCDBYe6ZUGdGYwMZZ1zvSKfl4qn1Ix8KvSyflN4sAmZZJAAQ");
            return ResponseFormatter::success($request->user(), 'Data User berhasil diambil');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => "Update profil gagal",
                'error' => $error,
            ],  'Update Failed', 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $data = $request->all();

            $user = $request->user();
            if ($request->password) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
                return ResponseFormatter::success(
                    $user,
                    'Change password Sukses',
                );
            }
            $user->update($data);

            return ResponseFormatter::success(
                $user,
                'Profile Updated',
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => "Update profil gagal",
                'error' => $error,
            ],  'Update Failed', 500);
        }
    }

    public function updatePhotoProfile(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'file' => 'required|image'
        ],);
        if ($validator->fails()) {
            return ResponseFormatter::error(
                [
                    'message' => "Gagal upload file",
                    'error' => $validator->errors()
                ],
                'update photo fails',
                401
            );
        }
        if ($request->file('file')) {


            $file = $request->file->store('assets/user', 'public');

            $user = $request->user();
            $lastFile = $user->photo;
            $user->photo = $file;
            $user->update();

            if ($lastFile) {
                if (Storage::disk('public')->exists($lastFile)) {
                    Storage::disk('public')->delete($lastFile);
                }
            }

            return ResponseFormatter::success($user, 'File successfully uploaded');
        }
    }


}
