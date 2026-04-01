<?php

namespace App\Services;

use App\Traits\AppCommonFunction;
use App\Models\GalleryImages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryService
{
    use AppCommonFunction;

    public function __construct(private GalleryImages $gallery) {}

    public function getGalleryImages($company_id = null)
    {
        if ($company_id) {
            return $this->gallery::where('company_id', $company_id)->latest()->get();
        }

        return $this->gallery::whereNull('company_id')->latest()->get();
    }

    public function storeImages($images)
    {
        $user = Auth::user();
        $data = [];

        foreach ($images as $image) {
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $file_name = 'image_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $relative_path = $user && $user->company_id
                    ? 'company-gallery-images/' . $user->company_id
                    : 'admin-gallery-images';

                $stored_path = $image->storeAs($relative_path, $file_name, 'public');
                $image_url   = asset('storage/' . $stored_path);

                $data[] = $this->saveImageRecord([
                    'user_id'    => $user ? $user->id : null,
                    'company_id' => $user && $user->company_id ? $user->company_id : null,
                    'filename'   => $file_name,
                    'image_path' => $image_url,
                ]);
            }
        }

        return $data;
    }

    private function saveImageRecord($data)
    {
        return $this->gallery::create($data);
    }

    public function deleteImage($id)
    {
        $image = $this->gallery::find($id);

        if (!$image) {
            return false;
        }

        $storage_path = str_replace(asset('storage') . '/', '', $image->image_path);
        Storage::disk('public')->delete($storage_path);
        $image->delete();

        return true;
    }
}
