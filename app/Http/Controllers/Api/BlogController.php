<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    // BREAD
    // B = BROWSE (menampilkan list data)
    // R = READ (menampilkan detail data)
    // E = EDIT (mengubah data)
    // A = ADD (menambahkan data)
    // D = DELETE (menghapus data)

    // blogs : untuk menampikan semua data blogs (GET)
    // B (Browse): MENAMPILKAN LISTS DARIPADA BLOG YANG ADA DI DATABASE
    public function browse(Request $request)
    {
        $blogs = Blog::with('user:id,name')
                    ->get()
                    ->transform(function($query) {
                        return [
                            'id' => $query->id,
                            'title' => $query->title,
                            'content' => $query->content,
                            'date' => $query->created_at?->diffForHumans(),
                            'user' => [
                                'id' => $query->user?->id,
                                'name' => $query->user?->name,
                            ],
                        ];
                    });

        return response()->json([
            'data' => $blogs
        ]);
    }

    // blog/{id} : untuk menampilkan data blog berdasarkan id (GET)
    // R (Read): MENAMPILKAN DETAIL DATA BLOG BERDASARKAN ID
    public function read($id, Request $request) {
        $blog = Blog::findOrFail($id);

        return response()->json([
            'message' => "Detail blog berhasil ditampilkan",
            'data' => $blog
        ]);
    }

     // blog/{id} : untuk mengupdate data blog berdasarkan id (PUT)
    // E (Edit): MENGUBAH DATA BLOG BERDASARKAN ID
    public function edit($id, Request $request) {
        $blog = Blog::findOrFail($id);

        $blog->update([
            'title' => request()->get('title'),
            'content' => request()->get('content'),
        ]);

        return response()->json([
            'message' => "Blog berhasil diubah",
        ]);
    }


    // blogs : untuk menambahkan data blog baru (POST)
    // A (Add): MENAMBAHKAN DATA BLOG BARU KE DALAM DATABASE
    public function add(Request $request) {
        $blog = Blog::create([
            'title' => request()->get('title'),
            'content' => request()->get('content'),
        ]);

        return response()->json([
            'message' => "Blog berhasil ditambahkan",
            'data' => $blog
        ]);
    }

    // blog/{id} : untuk menghapus data blog berdasarkan id (DELETE)
    // D (Delete): MENGHAPUS DATA BLOG BERDASARKAN ID
    public function delete($id, Request $request) {
        $blog = Blog::findOrFail($id);

        $blog->delete();

        return response()->json([
            'message' => "Blog berhasil dihapus",
        ]);
    }
}
