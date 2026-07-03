<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    /**
     * Display a listing of the resource with server-side filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('price', 'LIKE', "%{$search}%");
            });
        }
        $perPage = $request->integer('per_page', 5);

        $posts = $query->latest()->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Posts retrieved successfully.',
            'data'    => $posts
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(StorePostRequest $request): JsonResponse
    {
        $validatedData = $request->safe()->except(['images']);
        $uploadedImagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $uploadedImagesPaths[] = $path;
            }
        }
        $validatedData['images'] = $uploadedImagesPaths;
        $post = Post::create($validatedData);
        return response()->json([
            'status'  => true,
            'message' => 'Post created successfully.',
            'data'    => $post
        ], 201);
    }

    /**
     * Remove the specified resource from storage along with its assets.
     */
    public function destroy($id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Post not found.'
            ], 404);
        }
        if (!empty($post->images) && is_array($post->images)) {
            foreach ($post->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }

        $post->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Post deleted successfully.'
        ], 200);
    }
    public function show($id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'message' => 'Post not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Post retrieved successfully.',
            'data' => $post
        ], 200);
    }

    public function update(StorePostRequest $request, $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Post not found.'
            ], 404);
        }
        $validatedData = $request->safe()->except(['images']);
        $existingImagesRaw = $request->input('existing_images');
        $existingImages = [];
        if (is_string($existingImagesRaw)) {
            $existingImages = json_decode($existingImagesRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($existingImages)) {
                $existingImages = [];
            }
        } elseif (is_array($existingImagesRaw)) {
            $existingImages = $existingImagesRaw;
        }
        $currentImages = is_array($post->images) ? $post->images : [];
        $imagesToDelete = array_diff($currentImages, $existingImages);
        foreach ($imagesToDelete as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }
        $uploadedImagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $uploadedImagesPaths[] = $path;
            }
        }
        $validatedData['images'] = array_merge($existingImages, $uploadedImagesPaths);
        $post->update($validatedData);
        return response()->json([
            'status'  => true,
            'message' => 'Post updated successfully.',
            'data'    => $post
        ], 200);
    }
}
