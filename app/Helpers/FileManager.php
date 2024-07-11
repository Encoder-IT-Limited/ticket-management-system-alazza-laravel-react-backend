<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function uploadFile($file, $path = 'files', $name = ''): string
{
    // Upload file to public
//    $file_name = $name . '_' . time() . rand() . '.' . $file->getClientOriginalExtension();
//    $path = 'uploads/' . $path;
//    $file->move(public_path($path), $file_name);
//    return $path . '/' . $file_name;

    // Upload file to storage
    // $file_name = $name . '_' . time() . '.' . $file->extension();
    $file_name = Str::slug($name) . '-' . time() . rand() . '.' . $file->getClientOriginalExtension();
    $file->storeAs('public/' . $path . '/', $file_name);

    return $path . '/' . $file_name;
}

function upload_file($file, $path = 'files', $name = ''): string
{
    $path = Storage::disk('public')->putFile($path, $file);
    return basename($path);
}

function deleteFile($photo): void
{
    // Delete file from public
//    if (file_exists(public_path($photo))) {
//        unlink(public_path($photo));
//    }
    // Delete file from storage
    if (file_exists(storage_path('app/public/' . $photo))) {
        unlink(storage_path('app/public/' . $photo));
    }
}

function uploadFiles($photos, $path = 'files/'): array
{
    $file_names = [];
    foreach ($photos as $photo) {
        $file_names[] = uploadFile($photo, $path);
    }
    return $file_names;
}

function deleteFiles($photos): void
{
    foreach ($photos as $photo) {
        deleteFile($photo);
    }
}
