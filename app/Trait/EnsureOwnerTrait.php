<?php

namespace App\Trait;

use Illuminate\Http\Request;
use App\Models\Blog;

trait EnsureOwnerTrait
{
    private function ensureOwner(Request $request, Blog $blog): void
    {
        abort_unless($blog->user_id === $request->user()->id, 403, 'Blog ini hanya dapat diakses oleh pemiliknya.');
    }
}
