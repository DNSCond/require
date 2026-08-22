<?php use ANTHeader\ANTNavOption;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head3;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/header3/head3.php";
$http_RedirectStatus = (int)(array_key_exists('REDIRECT_STATUS', $_SERVER) ? "{$_SERVER["REDIRECT_STATUS"]}" : '');
function http_RedirectStatus(int $status, string $h1, string $p): array
{
    return [$status, $h1, $p];
}

[$_, $title, $message] = match ($http_RedirectStatus) {
    404 => http_RedirectStatus(404, '404 NotFound!', 'your REQUEST could not be Found at ANTRequest.nl are you sure you entered the address correctly?'),
    403 => http_RedirectStatus(403, '403 Forbidden!', 'Forbidden (meaning you are not allowed to do this)'),
    400 => http_RedirectStatus(400, '400 BadRequest!', 'BadRequest!'),
    default => ['???', '????: unknown Redirect status', 'we had not anticipated you getting so far. so?'],
};
http_response_code($http_RedirectStatus);
create_head3($title, ['base' => '/', 'bread' => [
                array('text' => 'Favicond\'s Character Gallery', 'href' => 'https://ANTRequest.nl'),
                array('text' => 'Not Found + 404', 'href' => '/'),
        ],
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <div><p><?= $message ?></div>
</div>
