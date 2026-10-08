<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Media;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GalleryMediaController extends Controller
{
    // Replaces addMedia: POST /galleries/{gallery}/media
    public function store(Request $request, Gallery $gallery, MediaUploadService $uploadService)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240',
        ]);

        $newMedia = [];
        foreach ($request->file('files') as $file) {
            $fileData = $uploadService->uploadFile($file, "galleries/{$gallery->id}");
            $newMedia[] = $gallery->media()->create($fileData);
        }

        return response()->json([
            'message' => 'Files added successfully',
            'data' => $newMedia,
        ], 201);
    }

    /**
     * Remove the specified media item from storage and gallery.
     */
    public function destroy( Gallery $gallery, Media $medium, MediaUploadService $uploadService
    ): JsonResponse {
        try {
            // 1. Verify media belongs to this gallery via pivot table relationship
            $belongsToGallery = $gallery->media()->where('media.id', $medium->id)->exists();

            if (!$belongsToGallery) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Media item not found in gallery #' . $gallery->id
                ], 404);
            }

            // 2. Remove physical file from storage
            if (!empty($medium->file_path)) {
                $uploadService->deleteFile($medium->file_path);
            }

            // 3. Delete media record from database
            $medium->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'File deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Media deletion failed: ' . $e->getMessage(), [
                'gallery_id' => $gallery->id,
                'media_id'   => $medium->id,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete file from gallery.'
            ], 500);
        }
    }
}


