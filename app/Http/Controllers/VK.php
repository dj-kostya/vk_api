<?php

namespace App\Http\Controllers;

use CURLFile;


class VK extends Controller
{
    protected $access_token = '1978aa26bad7828691bf5a56ba6e3bc06425d08028b5257753d4a0f6b2f64a6dfdb5a170c665737eb38e6';
    public $version  = '5.92';
    public $base_url = 'https://api.vk.com/method';

    protected $group_id = '176687426';
    public $owner_id = '-176687426';

    public $addPostUrl = '/wall.post';
    public $getWallServeUrl ='/photos.getWallUploadServer';
    protected $img_path = '/img/';

    /**
     * @param $url
     * @return string
     */
    public function sendPhotoByUrl($url)
    {
        $new_path = $_SERVER['DOCUMENT_ROOT'].$this->img_path.md5($url).'.jpg';
        $image = @file_get_contents($url);

        if ($image !== null)
        {
            file_put_contents($new_path, $image);
            $attach = @$this->sendPhotoToServer($new_path);
            unlink($new_path);
        }else {
            $attach = null;
        }
        return $attach;
        #dd($image);
    }

    /**
     * @param $path
     * @return string
     */
    private function sendPhotoToServer($path)
    {
        #$serverUrl=$this->getWallUploadServer();
        $serverUrl = $this->get('photos.getWallUploadServer',array(
            'group_id'=>$this->group_id,
        ))->response->upload_url;

        $aPost = array(
            'file' => new CURLFile($path)
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverUrl);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $aPost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec ($ch);
        curl_close($ch);
        $res = json_decode($res);
        #dd($res);
        $photo = $this->get('photos.saveWallPhoto', array(
            'photo'=>$res->photo,
            'server'=>$res->server,
            'hash'=>$res->hash,
            'group_id'=>$this->group_id
        ));
        return 'photo'.$photo->response[0]->owner_id.'_'.$photo->response[0]->id;
    }

    /**
     * @param $url
     * @param $parameters
     * @return mixed
     */
    public function get($url, $parameters)
    {
        $def_param = array(
            'access_token' => $this->access_token,
            'v'=>$this->version,
        );
        $parameters = array_merge($parameters,$def_param);

        $get_param = http_build_query($parameters);

        $response = json_decode( file_get_contents($this->base_url.'/'.$url.'?'.$get_param ));

        return $response;
    }

    /**
     * @param $message
     * @param null $attachments
     * @return mixed
     */
    public function addPost($message, $attachments = NULL)
    {
        $parametrs = array(
            'access_token' => $this->access_token,
            'v'=>$this->version,
            'owner_id'=>$this->owner_id,
            'message' =>$message,
            'attachments'=>$attachments
        );
        $get_param = http_build_query($parametrs);
        $response = json_decode( file_get_contents($this->base_url.$this->addPostUrl.'?'.$get_param ));
        return $response;
    }
}
