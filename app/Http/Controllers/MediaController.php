<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Media $media)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $media)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Media $media)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media): \Illuminate\Http\JsonResponse
    {
        try {
            if (file_exists(public_path($media->path))) {
                unlink(public_path($media->path));
            }
            $media->delete();

            return $this->success('Media deleted successfully');
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }
    }

    public function download(Media $media)
    {
//        return $media;
        return response()->download(public_path($media->path), $media->file_name . $media->extension);
    }
}
