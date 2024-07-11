<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Search\Article\SearchRepository;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'posts' => Article::all()
        ], 200);
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
        $request->validate([
            'title'   => ['required', 'string', 'max:100'],
            'body'    => ['required', 'string', 'max:100'],
            'tags'    => ['required', 'array'],
        ]);

        $data = $request->except('_token');
        $article = Article::create($data);

        if ($article) {
            return response()->json([
                'article' => $article
            ], 200);
        }

        return response()->json([
            'message' => __('Failed to create post.')
        ], 500);
    }

    /**
     * Display the specified resource.
     */
    public function search(Request $request, SearchRepository $searchRepository)
    {
        $search = $request->get('search');

        return response()->json([
            'search' => $search
                ? $searchRepository->search($search)
                : Article::all(),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
