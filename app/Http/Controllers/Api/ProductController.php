<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all posts
        $product = Product::where('is_delete', 0)->get();

        //return collection of posts as a resource
        return new ProductResource(true, 'List Data Product', $product);
    }
}
