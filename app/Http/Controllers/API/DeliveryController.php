<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\Delivery;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProductDelivery;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function all(Request $request)
    {
        try {
            $id = $request->input('id');
            $no_resi = $request->input('no_resi');
            $courier_id = $request->input('courier_id');
            $status = $request->input('status');
            $isNotDone = $request->input('isNotDone');

            

            if ($id) {
                $partner = Delivery::find($id);
                if ($partner) {
                    return ResponseFormatter::success($partner, "Get Delivery Success");
                } else {
                    return ResponseFormatter::error([
                        'message' => "Data Delivery tidak ditemukan"
                    ], "Get Deliery failed");
                }
            }
            $value = Delivery::query();

            if ($no_resi) {
                $value->where('no_resi', 'like', '%' . $no_resi . '%');
            }
            if ($courier_id) {
                $value->where('courier_id', $courier_id);
            }

            if($status){
                $value->where('status', $status);
            }

            if($isNotDone){
                $value->where('status','!=','Berhasil');
            }
            

            return ResponseFormatter::success($value->orderBy('created_at', 'desc')->get(), "Get Delivery Success");

        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Get Delivery Failed");
        }
    }

    public function update(Request $request)
    {

        try {
            DB::beginTransaction();

            Validator::make($request->all(), [
                'partner_id' => 'required',
                'courier_id' => 'required',
            ]);

            $data = $request->all();
            $data['no_resi'] = Carbon::now()->getPreciseTimestamp(3);
            if($request->status){
                if($request->status =='Berhasil'){
                    $data['date_received'] = Carbon::now()->format('Y-m-d H:i:s');
                }
                if($request->status =='On Progress'){
                    $data['date_delivery'] = Carbon::now()->format('Y-m-d H:i:s');
                }
            }
            $result =  Delivery::updateOrCreate([
                'id' => $request->id,
            ], $data);
            ProductDeliveryController::createDelete($request, $result->id);
            $result = Delivery::find($result->id);
            if ($request->file) {
                $file = $request->file->store('assets/delivery', 'public');
                $result->photo_received = $file;
                $result->update();
            }
            DB::commit();

            return ResponseFormatter::success($result, "Update Delivery Success");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Update Delivery Failed");
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $result = Delivery::where('id', $request->id)->delete();

            return ResponseFormatter::success($result, "Delete Delivery Success");
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Delete Delivery Failed");
        }
    }

    public function calculateDistance($lokasi1_lat, $lokasi1_long, $lokasi2_lat, $lokasi2_long, $unit = 'm', $desimal = 2)
    {
        // Menghitung jarak dalam derajat
        $derajat = rad2deg(acos((sin(deg2rad($lokasi1_lat)) * sin(deg2rad($lokasi2_lat))) + (cos(deg2rad($lokasi1_lat)) * cos(deg2rad($lokasi2_lat)) * cos(deg2rad($lokasi1_long - $lokasi2_long)))));

        // Mengkonversi derajat kedalam unit yang dipilih (kilometer, mil atau mil laut)
        switch ($unit) {
            case 'm':
                $jarak = $derajat * 111.13384 * 1000; // 1 derajat = 111.13384 km, berdasarkan diameter rata-rata bumi (12,735 km)
                break;
            case 'mi':
                $jarak = $derajat * 69.05482; // 1 derajat = 69.05482 miles(mil), berdasarkan diameter rata-rata bumi (7,913.1 miles)
                break;
            case 'nmi':
                $jarak = $derajat * 59.97662; // 1 derajat = 59.97662 nautic miles(mil laut), berdasarkan diameter rata-rata bumi (6,876.3 nautical miles)
        }
        return round($jarak, $desimal);
    }
}



