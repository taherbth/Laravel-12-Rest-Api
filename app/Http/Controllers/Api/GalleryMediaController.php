<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Media;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

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

    // Replaces deleteMedia: DELETE /galleries/{gallery}/media/{media}
    public function destroy(Gallery $gallery, Media $media, MediaUploadService $uploadService)
    {
        $uploadService->deleteFile($media->file_path);
        $media->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
}


