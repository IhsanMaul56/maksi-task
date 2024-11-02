<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Jobs\ProcessProductUpload;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
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
            'category'      => 'required',
            'img'           => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Simpan file ke temporary storage
            $file = $request->file('img');
            $tempPath = $file->store('temp');
            $originalName = $file->getClientOriginalName();

            //create product
            $product = Product::create([
                'code'          => $request->code,
                'name'          => $request->name,
                'description'   => $request->description,
                'stock'         => $request->stock,
                'price'         => $request->price,
                'category'      => $request->category,
                'status'        => 'pending'
            ]);

            // Dispatch job
            ProcessProductUpload::dispatch(
                $request->except('img'),
                $tempPath,
                $originalName
            );

            return new ProductResource(true, 'Product upload in progress', $product);
        } catch (\Exception $e) {
            // Hapus temporary file jika ada error
            if (isset($tempPath)) {
                Storage::delete($tempPath);
            }

            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'category'      => 'required',
            'img'           => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
        $product = Product::find($id);

        //check if image is not empty
        if ($request->hasFile('img')) {

            //upload image
            $image = $request->file('img');
            $image->storeAs('public/product', $image->hashName());

            //delete old image
            Storage::delete('public/product/' . basename($product->image));

            //update post with new image
            $product->update([
                'name'          => $request->name,
                'description'   => $request->description,
                'stock'         => $request->stock,
                'price'         => $request->price,
                'category'      => $request->category,
                'img'           => $image->hashName()
            ]);
        } else {

            //update post without image
            $product->update([
                'name'          => $request->name,
                'description'   => $request->description,
                'stock'         => $request->stock,
                'price'         => $request->price,
                'category'      => $request->category,
            ]);
        }

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
