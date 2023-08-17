<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{
    //Base CRUD
    public function index()
    {
        $question = Question::whereNull('deleted_at')->simplePaginate(20);

        return response()->json($question);
    }

    public function store(Request $request)
    {
        $question = Question::create($request->all());

        return response()->json($question, 201);
    }

    public function show($id)
    {
        $question = Question::where('_id', '=', $id)->first();
        return response()->json($question);
    }

    public function update(Request $request, $id)
    {
        $question = Question::where('_id', '=', $id)->first();
        $question->update($request->all());

        return response()->json($question);
    }

    public function destroy($id)
    {
        $question = Question::where('_id', '=', $id)->first();

        $question->delete();

        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $question = Question::where('_id', '=', $id)->first();

        if ($question->trashed()) {
            $question->restore();
        }

        return response()->json(null, 204);
    }

    public function approve(Request $request, $id)
    {
        //check role
        //request: email & password
        $this->sendHttpRequest(env('SERVICE_AUTH_URL') . '/checkUserInRole', 'post', $request);
        $question = $question = Question::where('_id', '=', $id)->first();
        $question->is_approved = true;
        $question->save();

        return response()->json($question);
    }
}
