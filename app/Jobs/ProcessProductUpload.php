<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessProductUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected array $productData,
        protected string $tempPath,
        protected string $originalName
    ) {}

    public function handle(): void
    {
        try {
            // Generate unique filename
            $imageName = time() . '_' . $this->originalName;

            // Pindahkan file dari temporary ke permanent storage
            Storage::move($this->tempPath, 'public/product/' . $imageName);

            // Update product
            Product::where('code', $this->productData['code'])->update([
                'img' => $imageName,
                'status' => 'completed'
            ]);

        } catch (\Exception $e) {
            // Hapus temporary file jika ada error
            Storage::delete($this->tempPath);
            
            // Update status product menjadi failed
            Product::where('code', $this->productData['code'])->update([
                'status' => 'failed'
            ]);

            throw $e;
        }
    }
}
