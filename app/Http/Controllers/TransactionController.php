<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{

    // Max amount we can payout in one transaction
    private $maxValueOfTransactions = 1000;

    /**
     * It will create spliced transactions and save in the database
     * @param Request $req
     * @return JsonResponse
     */
    public function create(Request $req):JsonResponse
    {
        $validate = Validator::make($req->all(),
        [
           'products'=>'required|array',
            'sellerId'=>'required'
        ]);

        if($validate->fails()){
            return response()->json(['res'=>false,'msg'=>$validate->errors()]);
        }

        $products = ProductController::initProducts($req->products);
        $transactions = $this->splitProducts($products);
        $this->saveTransactions($transactions,$req->sellerId);

        return response()->json(['res'=>true]);

    }


    /**
     * We need to check products currencies first , to group up products with same currency_id,
     * so we will create spliced payouts for each group of currencies
     * ( we can not convert product prices into only one currency because of the change rate is always changing )
     *
     * After that we also need to check if each group do not exceed the max amount of payment,
     * if so , we need to split them again
     *
     * @param Collection $products
     * @return array
     */
    public function splitProducts(Collection $products): array
    {
        $spliced = [];
        $currencies = Currency::all();
        foreach($currencies as $currency){
            //get products with the currency we need
            $theProducts = $products->filter(function($item) use ($currency){
                return $item->currency_id == $currency->id;
            });

            //split the products by their sum price
            $theProducts = $this->splitProductsByMaxSumPrice($theProducts);
            $spliced[$currency->id] = $theProducts;
        }

        return $spliced;

    }


    /**
     * Checks if the products sum price is bigger than the limit
     * splits the array
     *
     * @param Collection $products
     * @return object
     */
    private function splitProductsByMaxSumPrice(Collection $products): object
    {
        $spliced = [];
        $sum = 0;
        $splicedCount = 0;
        foreach($products as $product){
            // Need to have SUM value of these products
            $sum += $product->price;

            //Exceeded the limit , so lets create a new array block and reset the SUM value
            if($sum > $this->maxValueOfTransactions){
                $sum = 0;
                $splicedCount++;
            }
            $spliced[$splicedCount]['sum'] = $sum;
            $spliced[$splicedCount]['products'][] = $product;
        }

        return (Object) $spliced;
    }

    private function saveTransactionProducts($products,$transactionId):void
    {
        foreach($products as $product){
            $trProductObj = new TransactionProduct();
            $trProductObj->product_id = $product->id;
            $trProductObj->transaction_id = $transactionId;
            $trProductObj->save();
        }
    }


    /**
     * ToDo: Rollback the database if something bad happened
     * @param $transactions
     * @param $sellerId
     * @return bool
     */
    private function saveTransactions($transactions, $sellerId):bool
    {
        foreach($transactions as $currencyId=>$transaction){
            //this currency has no transactions
            if(empty($transaction))
                continue;
            foreach($transaction as $tr){
                $trObj = new Transaction();
                $trObj->amount = $tr['sum'];
                $trObj->currency_id = $currencyId;
                $trObj->seller_id = $sellerId;
                $trObj->save();
                $this->saveTransactionProducts($tr['products'],$trObj->id);
            }
        }
        return true;
    }

}
