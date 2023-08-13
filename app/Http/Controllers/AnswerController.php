<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;

class AnswerController extends Controller
{
    //Base CRUD
    public function index()
    {
        $data = Answer::whereNull('deleted_at')->simplePaginate (20);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $answer = Answer::create($request->all());

        return response()->json($answer, 201);
    }

    public function show($id)
    {
        $answer = Answer::where('_id', '=', $id)->first();
        return response()->json($answer);
    }

    public function update(Request $request, $id)
    {
        $answer = Answer::where('_id', '=', $id)->first();
        $answer->update($request->all());

        return response()->json($answer);
    }

    public function destroy($id)
    {
        $answer = Answer::where('_id', '=', $id)->first();

        $answer->delete();

        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $answer = Answer::where('_id', '=', $id)->first();

        if ($answer->trashed()) {
            $answer->restore();
        }

        return response()->json(null, 204);
    }
}
