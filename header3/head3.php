<?php namespace ANTHeader;

use function Helpers\htmlspecialchars12;
use function Helpers\sha256;

require_once __DIR__ . "/../helpers.php";
function create_head3(string $title, array $user_options): void
{
    $options = [
            'base' => getFrom($user_options, 'base'),
            'desc' => getFrom($user_options, 'desc'),
            'class' => getFrom($user_options, 'class', array()),
            'lang' => getFrom($user_options, 'lang', 'en'),
            'ventHref' => getFrom($user_options, 'ventHref', false),
            'linkarrays' => getFrom($user_options, 'bread', array()),
            'borderColor' => getFrom($user_options, 'borderColor', '#00a8f3'),
            'backColor' => getFrom($user_options, 'backColor', '#0073a6'),
            'stylelinks' => getFrom($user_options, 'stylelinks', array()),
            'metatags' => getFrom($user_options, 'metatags', array()),
            'linktags' => getFrom($user_options, 'linktags', array()),
    ];
    ob_start();
    $ventStatus_VentOn = false;
    $bottom = "<div class=bottom-divs>";
    $ventHref = '/gallery/char/window';
    if (is_string($options['ventHref'])) {
        $ventStatus_VentOn = true;
        $ventHref = $options['ventHref'];
    } ?>
    <div class=empty>
    <div></div>
    <div>
        <div>
            <a href="<?= $ventHref ?>">
                <svg width="1280" height="800" viewBox="0 0 1280 800"
                     xmlns="http://www.w3.org/2000/svg" class="special-event-ventilation">
                    <rect x="0" y="0" width="1280" height="800" fill="darkgray"/>
                    <g><?= '<=!-- (' . ($ventStatus_VentOn ? 'On' : 'Off') . ') --=>';
                        $ventColorL = ($ventStatus_VentOn ? '#fd9455' : '#36393f');
                        $ventColorD = ($ventStatus_VentOn ? '#fc6912' : '#36393f');
                        $attrs = 'stroke=gray stroke-width=16 paint-order=\'stroke\'';
                        for ($i = 0; $i < 6; $i++) {
                            $j = $i * 200 + 100;
                            echo "<rect x=$j y=80  width=80 height=640 fill='$ventColorL' $attrs/>" .
                                    "<rect x=$j y=550 width=80 height=170 fill='$ventColorD'/>\x20";
                        } ?></g>
                </svg>
            </a>
        </div>
    </div>
    </div><?= "\n</div>";
    $bottom = $bottom . preg_replace('/\\s+/', ' ', ob_get_clean());
    $bottom = str_replace('> <', ">\n<", $bottom);
    $importmap = json_encode([
            'imports' => new \stdClass,
    ]);
    $importHash = 'sha256-' . base64_encode(hash('sha256', $importmap, true));
    header("Content-Security-Policy: default-src 'none'; img-src 'self' blob:; style-src 'self'; " .
            "script-src 'self' https://keepandroidopen.org/banner.js '$importHash'; frame-ancestors 'none'; " .
            "base-uri 'self'; font-src 'none'; upgrade-insecure-requests; frame-src 'none'; form-action 'self'");
    ob_start(function (string $string) use ($bottom): string {
        return "$string$bottom\n";
    });
    $bgColor = '#0073a6';
    $borderColor = '#00a8f3';
    if (array_key_exists('borderColor', $options) &&
            array_key_exists('backColor', $options)
            && preg_match('/^(#?[a-fA-F0-9]{6}),(#?[a-fA-F0-9]{6})$/D',
                    "{$options['borderColor']},{$options['backColor']}",
                    $matches)) {
        [, $borderColor, $bgColor] = $matches;
    }
    $title = htmlspecialchars12("$title (ANTRequest.nl)");
    $base = !empty($options['base']) ? "<base href=\"{$options['base']}\">" : '<!--base/-->';
    echo "<!DOCTYPE html><html lang=\"{$options['lang']}\" data-p=$borderColor data-s=$bgColor>" .
            "<meta charset=UTF-8><title>$title</title>$base\n<script type=importmap>$importmap" .
            "</script><script type=module src=/require/JSONScript.js></script>\n\n";
    echo "<meta name=viewport content=width=device-width,initial-scale=1>";
    foreach (['/require/head2/ANTStylesheet.css', '/require/Nav.css'] as $stylelink) {
        echo "\n<link href=$stylelink rel=stylesheet>";
    }
    foreach ($options['stylelinks'] as $stylelink) {
        $stylelink = htmlspecialchars12($stylelink);
        echo "\n<link href='$stylelink' rel=stylesheet>";
    }

    echo "\n<link rel=icon href=/favicon.ico>";
    if (is_string($options['desc'])) {
        $desc = htmlspecialchars12($options['desc']);
        echo "\n<meta name=description content='$desc'>";}

    foreach ($options['metatags'] as $metatag) {
        if (is_null($metatag)) continue;
        $name = htmlspecialchars12($metatag[0]);
        $cont = htmlspecialchars12($metatag[1]);
        echo "\n<meta name='$name' content='$cont'>";
    }

    if ($canonical = getFrom($user_options, 'canonical'))
        echo "<link href='$canonical' rel=canonical>";
    $class = '"' . htmlspecialchars12(implode("\x20", $options['class'] ?? array())) . '"';
    /** @noinspection HtmlUnknownTarget */
    echo "\n<script src=/require/head2/domContentLoadedPromise.js></script>";
    echo "<body class=$class>\n<nav class=headernav><div>\n\n</div></nav>";
    if ($linkarrays = $options['linkarrays'] ?? array(['text' => 'ANTRequest.nl', 'href' => 'https://antrequest.nl/'])) {
        echo "<nav class=breadcrumbs-list><div><ol>";
        if (!is_null($arr = array_shift($linkarrays))) {
            $text = htmlspecialchars12("{$arr['text']}");
            $href = htmlspecialchars12("{$arr['href']}");
            echo "\n<li><a href='$href' aria-current=page>$text</a>";
        }
        foreach ($linkarrays as $arr) {
            if (is_null($arr)) continue;
            $text = htmlspecialchars12("{$arr['text']}");
            $href = htmlspecialchars12("{$arr['href']}");
            echo "\n<li><a href='$href'>$text</a>";
        }
        echo "</ol></div></nav>";
    }
}

function getFrom(array $array, string|int $property, mixed $default = null): mixed
{
    return array_key_exists($property, $array) ? ($array[$property] ?? $default) : $default;
}
