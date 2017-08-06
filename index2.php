<?php
/**
 * Created by IntelliJ IDEA.
 * User: asm
 * Date: 8/6/17
 * Time: 9:00 AM
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'Classes/App.php';
include_once 'Classes/City.php';
include_once 'Classes/Commerce.php';
include_once 'Classes/Country.php';

class BnParser2 extends \parser\App
{

    function __construct()
    {
        $this->getTypes();
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

$parser = new BnParser2();

if ($_POST) {
    if (strpos($_POST['type'], 'city') !== false) {
        $parser = new \parser\City();

    } elseif (strpos($_POST['type'], 'country') !== false) {
        $parser = new \parser\Country();

    } elseif (strpos($_POST['type'], 'commerce') !== false) {
        $parser = new \parser\Commerce();
    }


    $html = $parser->run();
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
                        <? if (isset($_POST['type']) && $_POST['type'] == $url): ?>
                            <option value="<?= $url ?>" selected><?= $txt ?></option>
                        <? else: ?>
                            <option value="<?= $url ?>"><?= $txt ?></option>
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
    <?= $html ?>
<? endif; ?>
</body>
</html>
