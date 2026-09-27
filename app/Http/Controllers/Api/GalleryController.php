<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use App\Http\Requests\GalleryRequest;


class GalleryController extends Controller
{
    public function index()
    {
        return response()->json(Gallery::with('media')->latest()->get());
    }

    public function store(GalleryRequest $request, MediaUploadService $uploadService)
    {
        $validated = $request->validated();

        $gallery = $uploadService->createGalleryWithFiles(
            $validated,
            $request->file('cover_photo'),
            $request->file('files', [])
        );

        return response()->json([
            'message' => 'Gallery created successfully',
            'data' => $gallery->load('media'),
        ], 201);
    }

    public function destroy(Gallery $gallery, MediaUploadService $uploadService)
    {
        $uploadService->deleteFile($gallery->cover_photo);

        foreach ($gallery->media as $item) {
            $uploadService->deleteFile($item->file_path);
        }

        $gallery->delete();

        return response()->json(['message' => 'Gallery deleted successfully']);
    }
}