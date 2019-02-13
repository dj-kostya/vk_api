<?php

namespace App\Http\Controllers;

use DOMDocument;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Sunra\PhpSimple\HtmlDomParser;

class kinopoisk
{
    private $baseSearhUrl = 'https://www.kinopoisk.ru/index.php?kp_query=';
    private $apiRating = 'https://rating.kinopoisk.ru/{film_id}.xml';
    public function getIdByTitle($title)
    {
        $html = file_get_contents($this->baseSearhUrl.urlencode($title));
        #dd($html);
        $body =  HtmlDomParser::str_get_html($html);
        $id_kp = $body->find('div [class=element most_wanted]',0)->children(2)->children(0)->children(0)->getAttribute('data-id');
        return $id_kp;
    }
    public function getKpRatingById($id)
    {
        $xml = file_get_contents(str_replace('{film_id}',$id, $this->apiRating));
        $ratings = new SimpleXMLElement($xml);
        return $ratings->kp_rating;


    }

}
