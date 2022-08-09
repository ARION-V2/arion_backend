<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{
    public function all(Request $request)
    {
        try {
            $id = $request->input('id');

            if ($id) {
                $partner = Partner::find($id);
                if ($partner) {
                    return ResponseFormatter::success($partner, "Get specific partner Success");
                } else {
                    return ResponseFormatter::error([
                        'message' => "Data partner tidak ditemukan"
                    ], "Get specific partner failed");
                }
            }
            $rating = Partner::query();

            return ResponseFormatter::success($rating->orderBy('created_at', 'desc')->get(), "Get Rating Success");
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Get Rating Failed");
        }
    }

    public function update(Request $request)
    {

        try {
            DB::beginTransaction();

            Validator::make($request->all(), [
                'full_name' => 'required',
                'market_name' => 'required',
                'address' => 'required',
                'no_hp' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
            ]);

            $data = $request->all();
            $result =  Partner::updateOrCreate([
               'id'=> $request->id,
            ], $data);

            $result = Partner::find($result->id);
            DB::commit();

            return ResponseFormatter::success($result, "Update Partner Success");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Update Partner Failed");
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $result = Partner::where('id', $request->id)->delete();

            return ResponseFormatter::success($result, "Delete Partner Success");
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Delete Partner Failed");
        }
    }
}
