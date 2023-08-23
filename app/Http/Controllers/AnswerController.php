<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use App\Models\ResponseMsg;

class AnswerController extends Controller
{
    //Base CRUD
    public function index()
    {
        $data = Answer::whereNull('deleted_at')->simplePaginate(20);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $answer = Answer::create($request->all());
        $question = Question::where('_id', '=', $request->input('question_id'))->first();
        $question->num_of_answers+=1;
        $question->save();

        return response()->json($answer, 201);
    }

    public function show($id)
    {
        $question = Question::where('_id', '=', $id)->first();
        $answer = Answer::where('question_id', '=', $question->_id)->get();
        return response()->json(new ResponseMsg(200, 'Get Answers successfully!', $answer));
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
