<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use App\Http\Requests\GalleryRequest;
use Illuminate\Http\JsonResponse;
use Throwable;


class GalleryController extends Controller
{
    protected MediaUploadService $uploadService;

    public function __construct(MediaUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    public function index()
    {
        return response()->json(Gallery::with('media')->latest()->get());
    }

    public function store(GalleryRequest $request) : JsonResponse
    {
        $validated = $request->validated();

        $gallery = $this->uploadService->createGalleryWithFiles(
            $validated,
            $request->file('cover_photo'),
            $request->file('files', [])
        );

        // return response()->json([
        //     'message' => 'Gallery created successfully',
        //     'data' => $gallery->load('media'),
        // ], 201);

        if($gallery) {
            // Eager-load media relation if needed
            $gallery->load('media');
            return $this->sendResponse(new GalleryResource($gallery), 'Gallery created successfully.', 201);
        }
        return response()->json([
            'message' => 'Error: Gallery not created, Please try again'
        ], 500); 
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