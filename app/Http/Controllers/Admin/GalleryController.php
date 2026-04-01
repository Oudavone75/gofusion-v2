<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryImagesRequest;
use Illuminate\Support\Facades\DB;
use App\Services\GalleryService;

class GalleryController extends Controller
{
    protected $gallery_service;

    public function __construct(GalleryService $gallery_service)
    {
        $this->gallery_service = $gallery_service;
    }

    public function index()
    {
        try {
            $gallery_images = $this->gallery_service->getGalleryImages();
            return view('admin.gallery.index', compact('gallery_images'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load gallery images.');
        }
    }


    public function create()
    {
        return view('admin.gallery.create');
    }

    public function store(GalleryImagesRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->gallery_service->storeImages($request->gallery_images ?? []);
            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Images uploaded successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $deleted = $this->gallery_service->deleteImage($id);
            if ($deleted) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Image deleted successfully!'
                ]);
            }
            return response()->json([
                'status'  => 'error',
                'message' => 'Image not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong while deleting the image.'
            ], 500);
        }
    }
}
