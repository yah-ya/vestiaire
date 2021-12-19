<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
class ProductTest extends TestCase
{
    use DatabaseTransactions,DatabaseMigrations;

    public function test_if_can_send_products_and_save_transactions_list(){

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'CurrenciesTableSeeder']);
        $products = \App\Models\Product::factory()->count(5)->create()->toArray();
        $this->json('post','/transactions',['products'=>$products,'sellerId'=>1])
        ->seeJson([
            'res' => true,
        ]);

       $this->CheckTransactionsInDatabase($products);
    }


    public function test_if_sum_of_transactions_amount_equal_to_sum_of_products_price(){
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'CurrenciesTableSeeder']);
        $products = \App\Models\Product::factory()->count(5)->create();
        $sumOfProducts = $this->CalcSumOfProductPrices($products);

        $this->json('post','/transactions',['products'=>$products,'sellerId'=>1])
            ->seeJson([
                'res' => true,
            ]);

        $controller = new \App\Http\Controllers\TransactionController();
        $splitedProducts = $controller->splitProducts($products);
        $sumOfTransactions = 0;
        foreach($splitedProducts as $currency=>$sp){
            foreach($sp as $tr){
                $sumOfTransactions += $tr['sum'];
            }
        }

        $this->assertEquals($sumOfProducts,$sumOfTransactions,'Sum of products prices are not equal to Sum of transactions amount');

    }

    private function CheckTransactionsInDatabase($products){
        $controller = new \App\Http\Controllers\TransactionController();
        $products = \App\Http\Controllers\ProductController::initProducts($products);

        $splitedProducts = $controller->splitProducts($products);


        $whatToSee = [];
        foreach($splitedProducts as $currency=>$sp){
            foreach($sp as $tr){
                $whatToSee[] = [
                    'seller_id' => 1,
                    'currency_id' =>$currency,
                    'amount'=>$tr['sum']
                ];
            }
        }

        foreach($whatToSee as $ws) {
            $this->seeInDatabase('transactions', $ws);
        }
    }

    private function CalcSumOfProductPrices($products){
        $sum = 0;
        foreach($products as $product){
            $sum += $product->price;
        }
        return $sum;
    }
}
