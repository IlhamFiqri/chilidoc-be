<?php

namespace App\Helper;

use GuzzleHttp\Client;

class PredictHelper
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getPrediction($image)
    {
        $imagePath = $image->getPathname();
        $imageName = $image->getClientOriginalName();

        $baseUrl = env('MODEL_REST_API');

        $response = $client->request('POST', $baseUrl.'/predict', [
            'multipart' => [
                [
                    'name'     => 'image',
                    'contents' => fopen($imagePath, 'r'),
                    'filename' => $imageName,
                ],
            ],
        ]);

        // Handle response
        return json_decode($response->getBody()->getContents(), true);
    }
}