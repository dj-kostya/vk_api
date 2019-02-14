<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;


class Controller extends BaseController
{
    private $token = '7z@{0A}Z{mFXmV6WUVN#CA38';


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postRequestVK(Request $request)
    {
        $vk_api = new VK;
        $photoUri = $request->input("url");
        $message = $request->input("message");
        $token = $request->input("access_token");
        if ($request->has('access_token') and $token == $this->token) {
            if ($request->has('message') and strlen($message) > 0) {
                if (!$request->has('url') or strlen($photoUri) == 0) {
                    $res = $vk_api->get('wall.post', array(
                        'owner_id'=>$vk_api->owner_id,
                        'message' =>$message
                    ));
                } else {
                    $attach = $vk_api->sendPhotoByUrl($photoUri);
                    if (strlen($attach)>0)
                    {
                        $res = $vk_api->get('wall.post', array(
                            'owner_id'=>$vk_api->owner_id,
                            'message' =>$message,
                            'attachments'=>$attach
                        ));
                    }else
                        return response()->json(["error"=>'Error with upload photo'],500);
                }
                if (array_key_exists('post_id', $res->response)) {
                    $response = response()->json(
                        [
                            "post_id" => $res->response->post_id
                        ]
                    );
                } else
                    $response = response()->json(
                        [
                            "error" => 'Ошибка при создании поста',
                            "response" => $res->response
                        ], 500
                    );
            } else {
                $response = response()->json([
                    "error" => 'Неверные параметры'
                ], 400);
            }
        }
        else
            $response = response()->json([
                "error" => 'Неверный токен'
            ],401);
        return $response;
    }


}
