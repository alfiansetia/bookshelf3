<?php

namespace App\Http\Controllers;

use App\Services\BookMetadataLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookLookupController extends Controller
{
    /**
     * Autocomplete metadata buku (Google Books dulu, OpenLibrary
     * fallback) dengan cache DB. Dipakai BookForm: ketik judul,
     * pilih hasil, form terisi otomatis.
     */
    public function __invoke(Request $request, BookMetadataLookup $lookup): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        return response()->json($lookup->search($validated['q']));
    }
}
