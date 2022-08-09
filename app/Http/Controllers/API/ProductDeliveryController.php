<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\ProductDelivery;
use App\Helpers\ResponseFormatter;
use Illuminate\Routing\Controller;

class ProductDeliveryController extends Controller
{
    static function createDelete(Request $request, int $deliveryId)
    {
        try {
            if ($request->add_product) {
                $request->validate(
                    [
                        'add_product' => 'required|array',
                    ],
                );

                // dd('id Usaha'. $idUsaha);
                // var_dump($request->add_product);
                foreach ($request->add_product as $value=> $product) {
                    $checkProduct = ProductDelivery::where('product_id', $product['product_id'])->where('delivery_id', $deliveryId)->first();
                    if(!$checkProduct){
                        ProductDelivery::create([
                            'delivery_id'=>$deliveryId,
                            'product_id'=> $product['product_id'],
                            'quantity'=>$product['quantity'],
                        ]);    
                    } 
                }
            }
            if ($request->delete_product) {
                $request->validate(
                    [
                        'delete_product' => 'required|array',
                    ],
                );
                foreach ($request->delete_product as $value=> $productId) {
                     ProductDelivery::where('product_id', $productId)->where('delivery_id', $deliveryId)->delete();
                }
            }

        } catch (Exception $error) {
            throw new Exception("Error Processing Request Add Product Delivery", 1);
            
            return ResponseFormatter::error(
                [
                    'message' => $error->getMessage()
                ],
                'Product Develivery Update Failed',
            );
        }
    }
}
