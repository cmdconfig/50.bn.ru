<?php
/**
 * Created by IntelliJ IDEA.
 * User: asm
 * Date: 8/6/17
 * Time: 9:00 AM
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

class BnParser
{
    const DATA_LINK = 1;
    const DATA_TITLE = 2;
    const DATA_COUNT_PHOTO = 4;
    const DATA_ROOMS = 5;
    const DATA_DIMENSIONS = 6;
    const DATA_FLOOR = 7;
    const DATA_CONSTRUCTION = 8;
    const DATA_PRICE = 9;
    const DATA_CURRENCY = 10;


    protected $defaultSearchURL = 'http://www.50.bn.ru/sale/city/flats/?sort=price&sortorder=ASC&price[from]=&price[to]=';
    //http://www.50.bn.ru/sale/city/flats/?sort=price&sortorder=ASC&price%5Bfrom%5D=&price%5Bto%5D=
    //http://www.50.bn.ru/sale/city/flats/?sort=price&sortorder=ASC&price[from]=&price[to]=
    /**
     * @var string
     */
    public $baseURL = 'http://www.50.bn.ru';
    protected $searchURL = 'http://www.50.bn.ru/sale/%s/?sort=%s&sortorder=%s&price[from]=%s&price[to]=%s';

    private $baseHTML = '';

    public $types = [];

    public $maxRooms = 10;

    public $filterRooms = [];

    public $result = [];

    protected $filters = ['type', 'sort', 'sortOrder', 'priceFrom', 'priceTo'];

    protected $defaultOrder = 'ASC';

    protected $defaultType = 'city/flats';

    protected $defaultPhotoParam = '&only_photo=1';

    private $photo = false;

    public $rooms = [];

    function __construct()
    {
        $this->getTypes();
    }

    public function run()
    {
        $type = $this->defaultType;
        $sort = '';
        $sortOrder = $this->defaultOrder;
        $priceFrom = '';
        $priceTo = '';

        if (isset($_POST['rooms'])) {
            $this->rooms = $_POST['rooms'];
        }

        if (!empty($_POST['photo'])) {
            $this->photo = true;
        }

        if (!empty($_POST['type'])) {
            $type = $_POST['type'];
        }

        if (!empty($_POST['sort'])) {
            $sort = $_POST['sort'];
        }

        if (!empty($_POST['sortOrder'])) {
            $sortOrder = $_POST['sortOrder'];
        }

        if (!empty($_POST['priceFrom'])) {
            $priceFrom = $_POST['priceFrom'];
        }

        if (!empty($_POST['priceTo'])) {
            $priceTo = $_POST['priceTo'];
        }

        $this->baseHTML = iconv('cp1251', 'utf8',
            $this->getHTML($type, $sort, $sortOrder, $priceFrom, $priceTo));

        $resDiv = $this->getResultDIV();

        preg_match_all('#<tr onclick="tr_click\(this\)">(.*)</tr>#Uis', $resDiv, $rows);

        if (isset($rows[1])) {
            foreach ($rows[1] as $row) {
                $this->result[] = $this->getDataFromRow($row);
            }
        }
    }

    protected function getDataFromRow(string $row): array
    {
        preg_match('#<td><p><a href="([a-z0-9/]{5,})" class="underline" target="_blank" onclick="return false;">([а-яА-Я0-9\D]{5,})</a>(<a class="ico photo" title="">([0-9]{1,})</a>|)</p></td>
<td>([0-9\-\s]{1,})</td>
<td>([\d\s\-\/]{1,})</td>
<td>([\d\s\-\/]{1,})</td>
<td><abbr title="([\D]{1,})">[\D]{1,}</abbr></td>	<td><p><span><b>([\d\s\-\/]{2,})</b></span><br>([\D]{2,}).</p></td>#Uis', trim($row), $res);

        if (empty($res)) {
            preg_match('#<td><p><a href="([a-z0-9/]{5,})" class="underline" target="_blank" onclick="return false;">([а-яА-Я0-9\D]{5,})</a>(<a class="ico photo" title="">([0-9]{1,})</a>|)</p></td>
<td>([0-9\-\s]{1,})</td>
<td>([\d\s\-\/]{1,})</td>
<td>([\d\s\-\/]{1,})</td>
<td>([\<]{1})/td>	<td><p><span><b>([\d\s\-\/]{2,})</b></span><br>([\D]{2,}).</p></td>#Uis', trim($row), $res);
            $res[self::DATA_CONSTRUCTION] = '';
        }

        return $res;
    }


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

    private function getURL(string $url): string
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
}

$parser = new BnParser();

if ($_POST) {
    $parser->run();
}

?>

<html>
<head>
</head>
<body>
<form method="post" action="/">
    <table border="1">
        <tr>
            <td>Тип</td>
            <td colspan="3">Цена</td>
            <td>Комнаты</td>
            <td>С фото</td>
        </tr>
        <tr>
            <td>
                <select name="type">
                    <option value=""></option>

                    <? foreach ($parser->types as $txt => $url): ?>
                        <? if (strpos($url, 'city') !== false): ?>
                            <? if (isset($_POST['type']) && $_POST['type'] == $url): ?>
                                <option value="<?= $url ?>" selected><?= $txt ?></option>
                            <? else: ?>
                                <option value="<?= $url ?>"><?= $txt ?></option>
                            <? endif; ?>
                        <? endif; ?>
                    <? endforeach; ?>
                </select>
            </td>
            <td>
                <input type="text" name="priceFrom"
                       value="<?= isset($_POST['priceFrom']) ? $_POST['priceFrom'] : '' ?>">
            </td>
            <td> -</td>
            <td>
                <input type="text" name="priceTo" value="<?= isset($_POST['priceTo']) ? $_POST['priceTo'] : '' ?>">
            </td>
            <td>
                <select multiple name="rooms[]">
                    <? for ($i = 1; $i <= $parser->maxRooms; $i++): ?>
                        <? if (!empty($parser->rooms) && in_array($i, $parser->rooms)): ?>
                            <option value="<?= $i ?>" selected><?= $i ?></option>
                        <? else: ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <? endif; ?>
                    <? endfor; ?>
                </select>
            </td>
            <td>
                <input type="checkbox" name="photo" <?= isset($_POST['photo']) ? 'checked' : '' ?>>
            </td>
        </tr>
    </table>
    <input type="submit" value="Найти">
</form>
<hr>
<? if ($_POST && empty($parser->result)): ?>
    Ничего не надо
<? else: ?>

    <table>
        <thead>
        <tr>
            <td>Адрес</td>
            <td>Кол-во фото</td>
            <td>Ком.</td>
            <td>Общ. / Жил. / Кух.</td>
            <td>Этаж</td>
            <td>Дом</td>
            <td>Цена</td>
        </tr>
        </thead>
        <tbody>
        <? foreach ($parser->result as $key => $val): ?>
            <? if (!empty($parser->rooms) && in_array(trim($val[$parser::DATA_ROOMS]), $parser->rooms) || empty($parser->rooms)): ?>
                <tr>
                    <td><a href="<?= $parser->baseURL . $val[$parser::DATA_LINK] ?>"
                           target="_blank"><?= $val[$parser::DATA_TITLE] ?></a></td>
                    <td><?= $val[$parser::DATA_COUNT_PHOTO] ?></td>
                    <td><?= $val[$parser::DATA_ROOMS] ?></td>
                    <td><?= $val[$parser::DATA_DIMENSIONS] ?></td>
                    <td><?= $val[$parser::DATA_FLOOR] ?></td>
                    <td><?= $val[$parser::DATA_CONSTRUCTION] ?></td>
                    <td><?= $val[$parser::DATA_PRICE] ?></td>
                    <td><?= $val[$parser::DATA_CURRENCY] ?></td>
                </tr>
            <? endif; ?>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>
</body>
</html>
