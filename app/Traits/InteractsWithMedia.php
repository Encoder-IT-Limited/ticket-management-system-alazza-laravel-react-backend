<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait InteractsWithMedia
{

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function groupMediaByCollection()
    {
        return $this->media->groupBy('collection_name');
    }


    public function uploadMedia($file, $name = 'default', $collection = 'default'): ?bool
    {
        if ($file->isValid()) {
            $original_file_name = $file->getClientOriginalName();
            $file_extension = $file->getClientOriginalExtension();
            $size = $file->getSize();
            $mime_type = $file->getMimeType();

            $generated_name = Str::slug(($name ?? $original_file_name));

            $newFileName = $generated_name . '_' . time() . rand(0, 1000) . '.' . $file_extension;

            $path = '/uploads/' . $collection;
            if ($file->move(public_path($path), $newFileName)) {
                $this->media()->create([
                    'name' => $name,
                    'file_name' => $generated_name,
                    'original_name' => $original_file_name,
                    'collection_name' => $collection,
                    'mime_type' => $mime_type,
                    'extension' => $file_extension,
                    'disk' => 'public',
                    'size' => $size,
                    'path' => $path . '/' . $newFileName
                ]);
            }
            return true;
        }
        return null;
    }

    public function deleteAllMedia(): void
    {
        $medias = $this->media();

        foreach ($medias as $media) {
            if (file_exists(public_path($media->path))) {
                unlink(public_path($media->path));
            }
        }
        $medias->delete();
    }

    public function deleteMediaByCollection($collection_name): void
    {
        $medias = $this->media->where('collection_name', $collection_name)->get();

        foreach ($medias as $media) {
            if (file_exists(public_path($media->path))) {
                unlink(public_path($media->path));
            }
        }
        $this->media->where('collection_name', $collection_name)->delete();
    }

    public function deleteMediaById($media_id): void
    {
        $media = $this->media->find($media_id);
        if (file_exists(public_path($media->path))) {
            unlink(public_path($media->path));
        }
        $media->delete();
    }
}
