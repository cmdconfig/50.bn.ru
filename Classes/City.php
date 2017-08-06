<?php
/**
 * Created by IntelliJ IDEA.
 * User: asm
 * Date: 8/6/17
 * Time: 9:00 AM
 */

namespace parser;

class City extends app
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


    function __construct()
    {

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


    private function createHTML()
    {




$html = [];

$html[] = '
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
        <tbody>';

foreach ($this->result as $key => $val){
if (!empty($this->rooms) && in_array(trim($val[$this::DATA_ROOMS]), $this->rooms)){
$href = $this->baseURL . $val[$this::DATA_LINK];
$html[] = "             
                <tr>
                    <td><a href='{$href}

'
                           target='_blank'>{
    $val[self::DATA_TITLE]}</a></td>
                    <td>{
$val[DATA_COUNT_PHOTO] }</td>
                    <td><?= $val[DATA_ROOMS] ?></td>
                    <td><?= $val[DATA_DIMENSIONS] ?></td>
                    <td><?= $val[DATA_FLOOR] ?></td>
                    <td><?= $val[DATA_CONSTRUCTION] ?></td>
                    <td><?= $val[DATA_PRICE] ?></td>
                    <td><?= $val[DATA_CURRENCY] ?></td>
                </tr>
            <? elseif (empty($this->rooms)): ?>
                <tr>
                    <td><a href=" <?= $this->baseURL . $val[$this::DATA_LINK] ?>"
target="_blank"><?= $val[$this::DATA_TITLE] ?></a></td>
<td><?= $val[$this::DATA_COUNT_PHOTO] ?></td>
<td><?= $val[$this::DATA_ROOMS] ?></td>
<td><?= $val[$this::DATA_DIMENSIONS] ?></td>
<td><?= $val[$this::DATA_FLOOR] ?></td>
<td><?= $val[$this::DATA_CONSTRUCTION] ?></td>
<td><?= $val[$this::DATA_PRICE] ?></td>
<td><?= $val[$this::DATA_CURRENCY] ?></td>
</tr>
";

</tbody>
</table>
</body>
</html>';


    }


}