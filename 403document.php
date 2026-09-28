<?php use function ANTHeader\create_head3;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";

http_response_code(403);
create_head3($title = '403 Forbidden!', [
        'base' => '/', 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                array('text' => 'Forbidden + 403', 'href' => '/'),
        ],
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <div><p>Forbidden (meaning you are not allowed to do this)</div>
</div>
