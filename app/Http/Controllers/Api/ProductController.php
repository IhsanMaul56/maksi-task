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
        return new ProductResource(true, 'Product successfully created!', $product);
    }

    public function show($id)
    {
        //find product by ID
        $product = Product::find($id);

        //check if product exists
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        //return single product as a resource
        return new ProductResource(true, 'Product successfully show!', $product);
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
        return new ProductResource(true, 'Product successfully updated!', $product);
    }

    public function destroy($id)
    {
        //find product by ID
        $product = Product::find($id);

        // check if product exists
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        //update is_delete column to 1
        $product->update(['is_delete' => 1]);

        //return response
        return new ProductResource(true, 'Product Successfully deleted', $product);
    }

    public function restore($id)
    {
        //find product by ID
        $product = Product::find($id);

        // check if product exists
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        //restore the product
        $product->update(['is_delete' => 0]);

        //return response
        return new ProductResource(true, 'Product Successfully restored', $product);
    }
}
