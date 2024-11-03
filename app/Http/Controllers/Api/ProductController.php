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
    /**
     * GET /api/admin/product
     *
     * Menampilkan daftar semua produk.
     * 
     * @access Admin, User
     * @response 200 OK - Daftar produk berhasil ditampilkan.
     * @response 401 Unauthorized - Jika pengguna tidak terautentikasi.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        //get data product
        $product = Product::where('is_delete', 0)->get();

        //return collection of product as a resource
        return new ProductResource(true, 'List Data Product', $product);
    }

    /**
     * POST /api/admin/product
     *
     * Menambahkan produk baru ke dalam sistem.
     * 
     * @access Admin only
     * @param string $code Kode unik produk.
     * @param string $name Nama produk.
     * @param string $description Deskripsi produk.
     * @param int $stock Jumlah stok produk.
     * @param int $price Harga produk.
     * @param string $category Kategori produk (contoh: 'leptop', 'hp').
     * @param int $is_delete Status penghapusan (0 untuk aktif, 1 untuk soft delete).
     * @param file $img Gambar produk.
     * 
     * @response 201 Created - Produk berhasil ditambahkan.
     * @response 403 Forbidden - Jika pengguna tidak memiliki hak akses.
     * @response 422 Unprocessable Entity - Jika data tidak valid.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * GET /api/admin/product/{id}
     *
     * Menampilkan detail produk berdasarkan ID.
     * 
     * @access Admin, User
     * @param int $id ID dari produk yang ingin ditampilkan.
     * 
     * @response 200 OK - Detail produk berhasil ditampilkan.
     * @response 404 Not Found - Jika produk tidak ditemukan.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * PUT /api/admin/product/{id}
     *
     * Memperbarui data produk berdasarkan ID.
     * 
     * @access Admin only
     * @param int $id ID dari produk yang ingin diperbarui.
     * @param string $code Kode unik produk.
     * @param string $name Nama produk.
     * @param string $description Deskripsi produk.
     * @param int $stock Jumlah stok produk.
     * @param int $price Harga produk.
     * @param string $category Kategori produk.
     * @param int $is_delete Status penghapusan (0 untuk aktif, 1 untuk soft delete).
     * @param file $img Gambar produk.
     * 
     * @response 200 OK - Produk berhasil diperbarui.
     * @response 403 Forbidden - Jika pengguna tidak memiliki hak akses.
     * @response 422 Unprocessable Entity - Jika data tidak valid.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * PUT /api/admin/product/{id}
     *
     * Mengubah status produk menjadi soft delete.
     * 
     * @access Admin only
     * @param int $id ID dari produk yang ingin dihapus atau dipulihkan.
     * @param int $is_delete Status penghapusan menjadi 1 untuk soft delete.
     * 
     * @response 200 OK - Status produk berhasil diubah.
     * @response 403 Forbidden - Jika pengguna tidak memiliki hak akses.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * PUT /api/admin/product/{id}
     *
     * Mengubah status produk menjadi restore.
     * 
     * @access Admin only
     * @param int $id ID dari produk yang ingin dihapus atau dipulihkan.
     * @param int $is_delete Status penghapusan menjadi 0 untuk restore.
     * 
     * @response 200 OK - Status produk berhasil diubah.
     * @response 403 Forbidden - Jika pengguna tidak memiliki hak akses.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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
