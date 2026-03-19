<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EcommerceController extends Controller
{
    public function index(): View
    {
        return $this->renderPage('index');
    }

    public function shop(): View
    {
        return $this->renderPage('shop');
    }

    public function bestseller(): View
    {
        return $this->renderPage('bestseller');
    }

    public function cart(): View
    {
        return $this->renderPage('cart');
    }

    public function cheackout(): View
    {
        return $this->renderPage('cheackout');
    }

    public function contact(): View
    {
        return $this->renderPage('contact');
    }

    public function notFound(): View
    {
        return $this->renderPage('404');
    }

    public function productDetails(string $slug): View
    {
        return $this->renderPage('product-details', ['slug' => $slug]);
    }

    private function renderPage(string $view, array $data = []): View
    {
        return view("ecommerce.theme1.{$view}", $data);
    }
}
