<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\ResponseMsg;

class QuestionController extends Controller
{
    //Base CRUD
    public function index()
    {
        $question = Question::whereNull('deleted_at')->orderBy('created_at', 'desc')->simplePaginate(20);

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
        $user_approved_id = $request->input('user_approved_id');
        $approved_at = $request->input('created_at');
        Question::where('_id', '=', $id)->update(['is_approved' => true]);
        Question::where('_id', '=', $id)->update(['user_approved_id' => $user_approved_id]);
        Question::where('_id', '=', $id)->update(['approved_at' => $approved_at]);

        //Send mail to admin

        return response()->json(new ResponseMsg(201, 'Approve sucessfully!', Question::where('_id', '=', $id)->first()));
    }

    public function vote(Request $request, $id)
    {
        $type = $request->input('type');
        $created_by = $request->input('created_by');
        $created_at = $request->input('created_at');
        $question = Question::where('_id', '=', $id)->where('interaction.created_by', '=', $created_by)->first();

        if ($question) {
            Question::where('_id', '=', $id)->where('interaction.created_by', '=', $created_by)->update(['interaction.$[].type' => $type]);
            if ($type === '1') {
                Question::where('_id', '=', $id)->update(['num_of_likes' => true]);
            } else {
                Question::where('_id', '=', $id)->update(['num_of_dislikes' => true]);
            }
        } else {
            $newInteraction = [
                'type' => $type,
                'created_by' => $created_by,
                'created_at' => $created_at
            ];

            Question::where('_id', '=', $id)->first()->push('interaction', $newInteraction);
        }

        return response()->json(new ResponseMsg(201, 'Interact sucessfully!', Question::where('_id', '=', $id)->first()));
    }

    public function report(Request $request, $id)
    {
        $content = $request->input('content');
        $created_by = $request->input('created_by');
        $created_at = $request->input('created_at');
        $question = Question::where('_id', '=', $id)->where('reports.created_by', '=', $created_by)->first();
        if ($question) {
            return response()->json(new ResponseMsg(200, 'Report was sent to admin!', null));
        } else {
            $newReport = [
                'content' => $content,
                'created_by' => $created_by,
                'created_at' => $created_at
            ];
            Question::where('_id', '=', $id)->first()->push('reports', $newReport);
            Question::where('_id', '=', $id)->update(['is_reported' => true]);

            return response()->json(new ResponseMsg(201, 'Report sucessfully!', Question::where('_id', '=', $id)->first()));
        }
    }
}
