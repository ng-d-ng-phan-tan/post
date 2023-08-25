<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\ResponseMsg;
use Elasticsearch\ClientBuilder;

class QuestionController extends Controller
{
    // Elasticsearch PHP client
    protected $elasticsearch;
    // Elastica client
    protected $elastica;
    // Elastica index
    protected $elasticIndex;

    public function __construct()
    {
        $this->elasticsearch = ClientBuilder::create()
            ->setHosts(config('database.connections.elasticsearch.hosts'))
            ->setBasicAuthentication('oMbvJYsWP4', 'c7CjNfwRXGiz2xDUt65dBg')
            ->build();
    }
    public function searchPostByTitleOrBody(Request $request)
    {
        try {
            if ($request->has('search')) {
                $search = $request->input('search');
            } else {
                return response()->json([
                    'message' => 'Please enter keyword'
                ], 400);
            }

            $offset = $request->has('offset') ? max(0, intval($request->input('offset'))) : 0;
            $limit = $request->has('limit') ? max(1, intval($request->input('limit'))) : 10;
            $params = [
                'index' => 'posts',
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $search,
                            'fields' => ['title', 'body']
                        ]
                    ]
                ],
                'from' => $offset,
                'size' => $limit
            ];
            $response = $this->elasticsearch->search($params);
            $hits = $response['hits']['hits'];
            $posts = [];
            foreach ($hits as $hit) {
                $posts[] = $hit['_source'];
            }
            return response()->json([
                'posts' => $posts,
                'total' => $response['hits']['total']['value'],
                'message' => 'Search successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //Base CRUD
    public function index()
    {
        $question = Question::whereNull('deleted_at')->where('is_approved', '=', true)->orderBy('created_at', 'desc')->simplePaginate(20);

        return response()->json($question);
    }

    public function getTop3Question()
    {
        $question = Question::where('is_approved', '=', true)->orderBy('num_of_answers', 'desc')->get();

        return response()->json(new ResponseMsg(200, 'Successfully!', $question));
    }

    public function store(Request $request)
    {
        $question = Question::create($request->all());

        $resUserObj = $this->sendHttpRequest(env('SERVICE_USER_URL') . '/getUser', 'post', [
            "user_id" => $question->questioner_id,
        ]);
        $user = $resUserObj->data[0];

        $responseLstEmailObj = $this->sendHttpRequest(env('SERVICE_USER_URL') . '/getLstReceiveEmail', 'post', null);

        $lstEmail = $responseLstEmailObj->data;
        $data = [
            "to" => $lstEmail,
            "subject" => "New Post",
            "data" => [
                "name" => $user->name,
                "email" => $user->email,
                "created_date" => $question->created_at,
                "title" => $question->title,
            ],
            "template" => "notification_admin"
        ];
        $res2 = $this->sendHttpRequest(env('SERVICE_NOTI_SENDMAIL_URL') . '/send', 'post', $data);

        return response()->json(new ResponseMsg(201, 'Create question successfully!', $question));
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

        $question = Question::where('_id', '=', $id);
        //Send mail to admin
        $resUserObj = $this->sendHttpRequest(env('SERVICE_USER_URL') . '/getUser', 'post', [
            "user_id" => $question->questioner_id,
        ]);
        $user = $resUserObj->data[0];
        $res2 = $this->sendHttpRequest(env('SERVICE_NOTI_SENDMAIL_URL') . '/send-notification', 'post', [

            "user_id" => $user->user_id,
            "title" => $question->title,
            "body" => $question->body,
            "device_token" => $user->device_token,
        ]);

        return response()->json(new ResponseMsg(201, 'Approve successfully!', Question::where('_id', '=', $id)->first()));
    }

    public function vote(Request $request, $id)
    {
        $type = $request->input('type');
        $created_by = $request->input('created_by');
        $created_at = $request->input('created_at');
        $question = Question::where('_id', '=', $id)->where('interaction.created_by', '=', $created_by)->first();

        if ($question) {
            //Re-vote
            if ($question->interaction[0]["type"] != $type) {
                if ($question->interaction[0]["type"] == '1') {
                    $question->num_of_likes -= 1;
                    $question->num_of_dislikes += 1;
                }

                if ($question->interaction[0]["type"] == '2') {
                    $question->num_of_likes += 1;
                    $question->num_of_dislikes -= 1;
                }
                Question::where('_id', '=', $id)->where('interaction.created_by', '=', $created_by)->update(['interaction.$[].type' => $type]);
            } else {
                if ($type == '1') {
                    $question->num_of_likes -= 1;
                }

                if ($type == '2') {
                    $question->num_of_dislikes -= 1;
                }
                Question::where('_id', '=', $id)->where('interaction.created_by', '=', $created_by)->pull('interaction', ['created_by' => $created_by]);
            }

            $question->save();
        } else {
            //New vote
            $newInteraction = [
                'type' => $type,
                'created_by' => $created_by,
                'created_at' => $created_at
            ];

            $question = Question::where('_id', '=', $id)->first();

            if ($type == '1') {
                $question->num_of_likes++;
            }
            if ($type == '2') {
                $question->num_of_dislikes++;
            }


            $question->push('interaction', $newInteraction);
            $question->save();
        }

        return response()->json(new ResponseMsg(201, 'Interact successfully!', Question::where('_id', '=', $id)->first()));
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

            return response()->json(new ResponseMsg(201, 'Report successfully!', Question::where('_id', '=', $id)->first()));
        }
    }

    public function search(Request $request)
    {
        $title = $request->input('title');
        $questions = Question::orWhere('title', 'like', '%' . $title . '%')->get();
        return response()->json(new ResponseMsg(201, 'Search successfully!', $questions));
    }

    public function getCount()
    {
        $response = new ResponseMsg("200", "Count", Question::count());
        return response()->json(($response));
    }
}
