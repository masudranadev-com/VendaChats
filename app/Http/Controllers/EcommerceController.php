<?php

namespace App\Http\Controllers;

class EcommerceController extends Controller
{
    public function index()
    {
        return view("ecommerce.theme1.index");
    }

    // Product 
    public function productDetails()
    {
        return view("ecommerce.theme1.product-details");
    }
}
