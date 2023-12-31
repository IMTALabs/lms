<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Exception;

class AiController extends Controller
{
    public function route_landing_page()
    {
        // các route của landing page
        $array = ['listening', 'writing', 'reading', 'writing'];
        return response()->json(['route' => $array]);
    }
    public function listening()
    {
        $listeningLink = request()->input('listen_link');
        $user_id = "1";
        if ($listeningLink) {
            preg_match('/\?v=(.*)/', $listeningLink, $matches);
            $youtubeId = $matches[1];
            $embedCode = '
          <iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . $youtubeId . '" title="How to convert Figma Design into Flutter Code | DhiWise.com" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
          ';
            try {
                $client = new Client();
                $response = $client->post('http://34.16.32.114:8150/gen_quizz', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'id' => $user_id,
                        'url' => $listeningLink,
                        'num_quizz' => 2,
                    ],
                ]);

                // Lấy mã trạng thái của response
                $statusCode = $response->getStatusCode();

                // Decode nội dung JSON từ response
                $body = json_decode($response->getBody(), true);

                // Hiển thị nội dung để kiểm tra
            } catch (Exception $e) {
                // Xử lý ngoại lệ nếu có lỗi
                $statusCode = $e->getCode();
                $body = json_decode($e->getResponse()->getBody(), true);

                // Hiển thị thông báo lỗi
                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body
                ]);
            }
            return response()->json([
                'data' => $body,
                'embedCode' => $embedCode
            ]);
        }
        return response()->json([
            'error' => 'Không có giá trị từ query parameter \'listen_link\'',
        ]);
    }
    public function writing_gen_instruction()
    {
        $user_id = "1";
        $topic = \request()->input('topic');
        try {
            $client = new Client();
            $response = $client->post('http://34.16.32.114:8300/gen_instruction', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'id' => $user_id,
                    'topic' => $topic,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
        } catch (Exception $e) {
            $statusCode = $e->getCode();
            $body = json_decode($e->getResponse()->getBody(), true);
            return response()->json([
                'statusCode' => $statusCode,
                'body' => $body
            ]);
        }
        return response()->json([
            'data' => $body,
        ]);
    }
    public function evalue()
    {
        $user_id = "1";
        $instruction=\request()->input('instruction');
        $submission = \request()->input('submission');
        try {
            $client = new Client();
            $response = $client->post('http://34.16.32.114:8300/evaluate', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'id' => $user_id,
                    'submission' => $submission,
                    'instruction'=>$instruction
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
        } catch (Exception $e) {
            $statusCode = $e->getCode();
            $body = json_decode($e->getResponse()->getBody(), true);
            return response()->json([
                'statusCode' => $statusCode,
                'body' => $body
            ]);
        }
        return response()->json([
            'data' => $body,
        ]);
    }
}
