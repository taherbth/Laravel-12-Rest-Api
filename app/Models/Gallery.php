<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'cover_photo'];
    protected $appends = ['cover_photo_url'];

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function getCoverPhotoUrlAttribute(): ?string
    {
        return $this->cover_photo ? asset('storage/' . $this->cover_photo) : null;
    }
}