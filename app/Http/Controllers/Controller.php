<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use GuzzleHttp\Client;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendHttpRequest($url, $type, $data)
    {
        $client = new Client();
        $response = null;
        if ($type == 'get') {
            $response = $client->get($url);
        } else {
            $response = $client->post($url, [
                'json' => $data,
            ]);
        }
        return json_decode($response->getBody()->getContents());
    }
}
