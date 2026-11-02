<?php

use Illuminate\Support\Facades\Route;
use App\Models\Blog;
use App\Http\Controllers\Api\AuthController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    Route::get('profile', [AuthController::class, 'profile']);
    
    // BREAD
    // B = BROWSE (menampilkan list data)
    // R = READ (menampilkan detail data)
    // E = EDIT (mengubah data)
    // A = ADD (menambahkan data)
    // D = DELETE (menghapus data)


    // blogs : untuk menampikan semua data blogs (GET)
    // B (Browse): MENAMPILKAN LISTS DARIPADA BLOG YANG ADA DI DATABASE
    Route::get('/blogs', function() {
        $blogs = Blog::get();

        return response()->json([
            'data' => $blogs
        ]);
    });

    // blog/{id} : untuk menampilkan data blog berdasarkan id (GET)
    // R (Read): MENAMPILKAN DETAIL DATA BLOG BERDASARKAN ID
    Route::get('/blog/{id}', function($id) {
        $blog = Blog::findOrFail($id);

        return response()->json([
            'message' => "Detail blog berhasil ditampilkan",
            'data' => $blog
        ]);
    });

    // blog/{id} : untuk mengupdate data blog berdasarkan id (PUT)
    // E (Edit): MENGUBAH DATA BLOG BERDASARKAN ID
    Route::put('/blog/{id}', function($id) {
        $blog = Blog::findOrFail($id);

        $blog->update([
            'title' => request()->get('title'),
            'content' => request()->get('content'),
        ]);

        return response()->json([
            'message' => "Blog berhasil diubah",
        ]);
    });


    // blogs : untuk menambahkan data blog baru (POST)
    // A (Add): MENAMBAHKAN DATA BLOG BARU KE DALAM DATABASE
    Route::post('/blogs', function() {
        $blog = Blog::create([
            'title' => request()->get('title'),
            'content' => request()->get('content'),
        ]);

        return response()->json([
            'message' => "Blog berhasil ditambahkan",
            'data' => $blog
        ]);
    });

    // blog/{id} : untuk menghapus data blog berdasarkan id (DELETE)
    // D (Delete): MENGHAPUS DATA BLOG BERDASARKAN ID
    Route::delete('/blog/{id}', function($id) {
        $blog = Blog::findOrFail($id);

        $blog->delete();

        return response()->json([
            'message' => "Blog berhasil dihapus",
        ]);
    });
});
