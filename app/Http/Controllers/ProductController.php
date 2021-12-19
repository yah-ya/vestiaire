<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    /**
     *
     * This function is to convert user products inputs into
     * array of objects of Product model
     * And checks if the inputs are valid products and are in our database or not
     *
     * @param $products
     * @return object
     * @throws \Exception
     */
    public static function initProducts(array $products):Collection
    {
        $productObjs = collect();
        foreach($products as $product){
            $productObj = Product::find($product['id']);
            if(!$productObj){
                throw new \Exception('The product is not found in the database');
            }
            $productObjs->add($productObj);
        }

        return $productObjs;
    }



}
