<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        //get data product
        $product = Product::where('is_delete', 0)->get();

        //return collection of product as a resource
        return new ProductResource(true, 'List Data Product', $product);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'code'          => 'required',
            'name'          => 'required',
            'description'   => 'required',
            'stock'         => 'required',
            'price'         => 'required',
            'category'      => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create product
        $product = Product::create([
            'code'          => $request->code,
            'name'          => $request->name,
            'description'   => $request->description,
            'stock'         => $request->stock,
            'price'         => $request->price,
            'category'      => $request->category
        ]);

        //return response
        return new ProductResource(true, 'Data Product Berhasil Ditambahkan!', $product);
    }

    public function show($id)
    {
        //find product by ID
        $product = Product::find($id);

        //return single product as a resource
        return new ProductResource(true, 'Detail Data Product!', $product);
    }

    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'description'   => 'required',
            'stock'         => 'required',
            'price'         => 'required',
            'category'      => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
        $product = Product::find($id);

        // uppdate product
        $product->update([
            'name'          => $request->name,
            'description'   => $request->description,
            'stock'         => $request->stock,
            'price'         => $request->price,
            'category'      => $request->category
        ]);

        //return response
        return new ProductResource(true, 'Data Post Berhasil Diubah!', $product);
    }
}
