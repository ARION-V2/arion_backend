<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');


            if ($id) {
                $partner = Product::find($id);
                if ($partner) {
                    return ResponseFormatter::success($partner, "Get Product Success");
                } else {
                    return ResponseFormatter::error([
                        'message' => "Data Product tidak ditemukan"
                    ], "Get Product failed");
                }
            }
            $value = Product::query();

            if ($name) {
                $value->where('name', 'like', '%' . $name . '%');
            }

            return ResponseFormatter::success($value->orderBy('created_at', 'desc')->get(), "Get Product Success");
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Get Product Failed");
        }
    }

    public function update(Request $request)
    {

        try {
            DB::beginTransaction();

            Validator::make($request->all(), [
                'product_name' => 'required',
                'description' => 'required',
                'file' =>'image',
                'prince' => 'required',
                'stock' => 'required',
            ]);

            $data = $request->all();
            $result =  Product::updateOrCreate([
                'id' => $request->id,
            ], $data);

            if ($request->file) {
                if ($request->file('file')) {
                    $file = $request->file->store('assets/products', 'public');

                    $result  = Product::find($result->id);
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

            $result = Product::find($result->id);
            DB::commit();

            return ResponseFormatter::success($result, "Update Product Success");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Update Product Failed");
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $result = Product::where('id', $request->id)->delete();

            return ResponseFormatter::success($result, "Delete Product Success");
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'error' => $e->getMessage(),
            ], "Delete Product Failed");
        }
    }
}
