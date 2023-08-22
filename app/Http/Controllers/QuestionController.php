<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use GuzzleHttp\Client;

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
        try {
            $token = $request->header('Authorization');
            $client = new Client();
            $res = $client->request('GET', 'http://localhost:8001/api/checkUserInRole?role=admin', [
                'headers' => ['Authorization', $token]
            ]);
            $status = $res->getStatusCode();
            if ($status == 200) {
                // $question = Question::create($request->all());

                return response()->json($res->getBody(), 201);
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $e->getResponse()->getBody()->getContents();
        }
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
        $is_approved = $request->input('is_approved');
        $question = $question = Question::where('_id', '=', $id)->first();
        $question->is_approved = $is_approved;
        $question->save();

        return response()->json($question);
    }

    public function vote(Request $request, $id)
    {
        $type = $request->input('type');
        $created_by = $request->input('created_by');
        $question = Question::where('_id', '=', $id)->where('interaction.created_by', '=', $created_by)->update(['interaction.$[].type' => $type]);
        return response()->json($question);
    }
}
