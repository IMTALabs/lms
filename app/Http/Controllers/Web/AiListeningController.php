<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AiListeningController extends Controller
{
   const PATH_VEIW = 'web.default.ai_listening.';
   public function index()
   {

      return view(self::PATH_VEIW . 'layout');
   }
   public function lab()
   {
      // Lấy giá trị từ query parameter 'listen_link'
      $listeningLink = request()->query('listen_link');

      // Lấy id của người dùng đã đăng nhập
      $user_id = "1";

      if ($listeningLink) {
         preg_match('/\?v=(.*)/', $listeningLink, $matches);
         $youtubeId = $matches[1];
         $embedCode = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . $youtubeId . '" title="How to convert Figma Design into Flutter Code | DhiWise.com" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
         $videoEmbedUrl = 'https://www.youtube.com/embed/' . $listeningLink;
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
         }
         return view(self::PATH_VEIW . 'lab', compact('body', 'embedCode'));
      }

      // Nếu không có giá trị 'listen_link', bạn có thể xử lý theo ý của mình
      // Ví dụ: redirect hoặc hiển thị thông báo
      return redirect()->back()->with('error', 'Không có giá trị từ query parameter \'listen_link\'');
   }
   public function reading()
   {
      return view(self::PATH_VEIW . __FUNCTION__);
   }
   public function writing()
   {
      return view(self::PATH_VEIW . __FUNCTION__);
   }
   public function speaking()
   {
      
      return view(self::PATH_VEIW . __FUNCTION__);
   }
}
