<?php

namespace parser;
/**
 * Created by IntelliJ IDEA.
 * User: asm
 * Date: 8/6/17
 * Time: 3:50 PM
 */

abstract class app
{

    protected $defaultSearchURL = 'http://www.50.bn.ru/sale/city/flats/?sort=price&sortorder=ASC&price[from]=&price[to]=';
    //http://www.50.bn.ru/sale/city/flats/?sort=price&sortorder=ASC&price%5Bfrom%5D=&price%5Bto%5D=
    //http://www.50.bn.ru/sale/city/flats/?sort=price&sortorder=ASC&price[from]=&price[to]=
    /**
     * @var string
     */
    public $baseURL = 'http://www.50.bn.ru';
    protected $searchURL = 'http://www.50.bn.ru/sale/%s/?sort=%s&sortorder=%s&price[from]=%s&price[to]=%s';

    protected $baseHTML = '';

    public $types = [];

    public $maxRooms = 10;

    public $filterRooms = [];

    public $result = [];

    protected $filters = ['type', 'sort', 'sortOrder', 'priceFrom', 'priceTo'];

    protected $defaultOrder = 'ASC';

    protected $defaultType = 'city/flats';

    protected $defaultPhotoParam = '&only_photo=1';

    protected $photo = false;

    public $rooms = [];

    public function run(){}

    protected function getDataFromRow(string $row): array{}

    protected function getResultDIV(): string
    {
        $result = '';
        preg_match("#<div class=\"result\">(.*)</div>#Uis", $this->baseHTML, $res);
        if (isset($res[1])) {
            if (preg_match("#<table>(.*)</table>#Uis", trim($res[1]), $div)) {
                $result = $div[1];
            }

        }

        if (is_null($result)) {
            $result = '';
        }

        return $result;
    }

    protected function getHTML(string $type, string $sort, string $sortOrder = 'ASC', string $priceFrom, string $priceTo): string
    {
        $url = sprintf($this->searchURL, $type, $sort, $sortOrder, $priceFrom, $priceTo);

        if ($this->photo) {
            $url = $url . $this->defaultPhotoParam;
        }

        return $this->getURL($url);
    }

    protected function getURL(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)');
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    protected function getTypes()
    {
        $html = iconv('cp1251', 'utf8', $this->getURL($this->baseURL));

        preg_match("#<select id=\"sct2\" name=\"type\" class=\"styled\" style=\"display:none\">(.*)</select>#Uis", $html, $select);
        $types = [];
        if (isset($select[1])) {
            preg_match_all('#<option value="([a-z/]{5,})">(.*)</option>#Uis', $select[1], $types);
            if (isset($types[1])) {
                foreach ($types[1] as $key => $val) {
                    $this->types[$types[2][$key]] = $types[1][$key];
                }
            }
        }
    }

}