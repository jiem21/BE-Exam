<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Validator;
use Auth;

class ProductsController extends Controller {
    public function __construct() {
    	$this->products = new Products;
    	$this->orders = new Orders;
        $this->user = new User;
    }

    public function index() {
        $products = $this->products->paginate(20);
        return $products;
    }

    public function show( $id ) {
        return $this->products->find( $id );
    }

    public function order( Request $request ) {
        $rules = [
            'product_id' => 'required',
            'quantity'   => 'required|integer',
        ];

        $validator = Validator::make( $request->all(), $rules );

        if ( $validator->fails() ) {
            return response()->json( [ 'message' => $validator->messages() ], 400 );
        } else {
            $product = $this->products->find( $request->product_id );

            if ( $product->available_stocks < $request->quantity ) {
                return response()->json( [ 'message' => 'Failed to order this product due to unavailablity of the stock' ], 400 );
            } else {
                $available_stock =  $product->available_stocks - $request->quantity;

                $product->update( [
                    'available_stocks' => $available_stock
                ] );

                $this->orders->create( [
                    'user_id'    => Auth::user()->id,
                    'product_id' => $request->product_id,
                    'stocks'     => $request->quantity
                ] );

                return response()->json( [ 'message' => 'You have successfully ordered this product' ], 201 ); 
            }
        }
    }
}
