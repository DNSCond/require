<?php use ANTHeader\ANTNavOption;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\create_head2;

require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
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
create_head2($title, ['base' => '/',
], [], [
        ANTNavFavicond('https://ANTRequest.nl', $title, false),
        new ANTNavOption('/',
                '/dollmaker2/icon/endpoint.php?bgcolor=%23000000&fgcolor=%2300ff00&L=%2300ff00&W=%23000000&LC=%2300ff00&RC=%2300ff00&accessory=lightbar',
                'void ANT', new Color('00a600'), new Color('00ff00'), true),
]) ?>
<div class=divs>
    <h1><?= $title ?></h1>
    <div><p><?= $message ?></div>
</div>
