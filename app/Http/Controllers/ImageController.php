<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class ImageController extends Controller
{
    /**
     * Upload and optimize image
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'type' => 'required|in:hotel,room',
            'id' => 'required|integer',
        ]);

        try {
            $image = $request->file('image');
            $type = $request->type;
            $id = $request->id;

            // Generate unique filename
            $filename = $type . '_' . $id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $type . 's/' . $filename;

            // Resize and optimize image
            $img = Image::make($image->getRealPath());
            
            // Resize to max 1920x1080 while maintaining aspect ratio
            $img->resize(1920, 1080, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Save optimized image
            $img->encode('jpg', 85); // 85% quality
            Storage::disk('public')->put($path, $img->stream());

            // Generate thumbnail
            $thumbnail = Image::make($image->getRealPath());
            $thumbnail->fit(400, 300);
            $thumbnail->encode('jpg', 80);
            $thumbnailPath = $type . 's/thumbnails/' . $filename;
            Storage::disk('public')->put($thumbnailPath, $thumbnail->stream());

            $url = Storage::disk('public')->url($path);
            $thumbnailUrl = Storage::disk('public')->url($thumbnailPath);

            return response()->json([
                'success' => true,
                'url' => $url,
                'thumbnail_url' => $thumbnailUrl,
                'path' => $path,
            ]);

        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload image. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete image
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            // Delete main image
            if (Storage::disk('public')->exists($request->path)) {
                Storage::disk('public')->delete($request->path);
            }

            // Delete thumbnail
            $thumbnailPath = str_replace('/images/', '/images/thumbnails/', $request->path);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Image deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete image.',
            ], 500);
        }
    }
}

