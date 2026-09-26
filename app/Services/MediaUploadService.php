<?php
namespace App\Services;
use App\Models\Gallery;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadService
{
    /**
     * Upload a single file physically and map metadata.
     */
    public function uploadFile(UploadedFile $file, string $folder = 'gallery', string $disk = 'public'): array
    {
        $mimeType = $file->getClientMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileType = $this->determineFileType($mimeType, $extension);

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = time() . '_' . Str::slug($originalName) . '.' . $extension;

        $path = $file->storeAs($folder, $fileName, $disk);

        return [
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $mimeType,
            'file_type' => $fileType,
            'file_size' => $file->getSize(),
        ];
    }
    /**
     * Create gallery record and attach files.
     */
    public function createGalleryWithFiles(array $data, UploadedFile $coverPhoto, array $files = []): Gallery
    {
        // 1. Upload Cover
        $coverData = $this->uploadFile($coverPhoto, 'covers');

        // 2. Create Gallery Model
        $gallery = Gallery::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'cover_photo' => $coverData['file_path'],
        ]);

        // 3. Attach Multiple Media
        foreach ($files as $file) {
            $fileData = $this->uploadFile($file, "galleries/{$gallery->id}");
            $gallery->media()->create($fileData);
        }
        return $gallery;
    }

    /**
     * Delete physical file from disk.
     */
    public function deleteFile(string $filePath, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }
        return false;
    }

    /**
     * Categorize files.
     */
    protected function determineFileType(string $mimeType, string $extension): string
    {
        if (str_contains($mimeType, 'image/')) {
            return 'image';
        }

        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip'])) {
            return 'document';
        }

        return 'other';
    }
}