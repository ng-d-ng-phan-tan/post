<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Tag;

class TagController extends Controller
{
    //Base CRUD
    public function index(Request $request)
    {
        $tag = Tag::query();

        if ($request->has('sort')) {
            if (Str::upper($request->sort) === "DESC" || Str::upper($request->sort) === "DESC") {
                $tag->orderBy('created_at', $request->sort);
            } else {
                $tag->orderBy('created_at', 'ASC');
            }
        }


        $data = $tag->whereNull('deleted_at')->simplePaginate(20);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $tag = Tag::create($request->all());

        return response()->json($tag, 201);
    }

    public function show($id)
    {
        $tag = Tag::where('_id', '=', $id)->first();
        return response()->json($tag);
    }

    public function update(Request $request, $id)
    {
        $tag = Tag::where('_id', '=', $id)->first();
        $tag->update($request->all());

        return response()->json($tag);
    }

    public function destroy($id)
    {
        $tag = Tag::where('_id', '=', $id)->first();

        $tag->delete();

        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $tag = Tag::where('_id', '=', $id)->first();

        if ($tag->trashed()) {
            $tag->restore();
        }

        return response()->json(null, 204);
    }
}
