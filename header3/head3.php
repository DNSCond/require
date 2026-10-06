<?php namespace ANTHeader;

use function Helpers\htmlspecialchars12;
use function Helpers\toDataSet;

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
            'cspConnectAllowed' => getFrom($user_options, 'cspConnectAllowed'),
            'localhostIconOverride' => getFrom($user_options, 'localhostIconOverride'),
            'noVent' => getFrom($user_options, 'noVent', false),
            'siteOverride' => getFrom($user_options, 'siteOverride'),
            'nightLightOverride' => getFrom($user_options, 'nightLightOverride', false),
    ];
    $iconPath = '/favicon.ico';
    $devHostFile = __DIR__ . '/../../../devhost.txt';
    if (is_string($options['localhostIconOverride'])) {
        $isDevHost = file_exists($devHostFile) && file_get_contents($devHostFile) === 'DevHost';
        if ($isDevHost && !array_key_exists('isntLocalhost', $_GET)) $iconPath = $options['localhostIconOverride'];
    }
    ob_start();
    $ventHref = '/';
    $ventStatus_VentOn = false;
    $bottom = "<div class=bottom-divs>";
    if (is_string($options['ventHref'])) {
        $ventHref = $options['ventHref'];
        $ventStatus_VentOn = true;
    }
    $ventStatus_VentOn = $ventStatus_VentOn || gmdate('m') === '12';
    if (!$options['noVent']): ?>
        <div class=empty>
        <div></div>
        <div>
            <div>
                <a href="<?= $ventHref ?>">
                    <svg width="1280" height="800" viewBox="0 0 1280 800"
                         xmlns="http://www.w3.org/2000/svg" class="special-event-ventilation">
                        <rect x="0" y="0" width="1280" height="800" fill="darkgray"/>
                        <g><?= '<!-- (' . ($ventStatus_VentOn ? 'On' : 'Off') . ') -->';
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
    else: echo "</div>"; endif;
    $bottom = $bottom . preg_replace('/\\s+/', ' ', ob_get_clean());
    $bottom = str_replace('> <', "><", $bottom);
    $conn = "\x20connect-src\x20";
    if (is_array($options['cspConnectAllowed']))
        $conn .= implode("\x20", $options['cspConnectAllowed']);
    $importmap = json_encode(['imports' => new \stdClass]);
    $importHash = 'sha256-' . base64_encode(hash('sha256', $importmap, true));
    header("Content-Security-Policy: default-src 'none'; img-src 'self' blob:; style-src 'self'"
            . "; script-src 'self' '$importHash'; frame-ancestors 'none'; upgrade-insecure-requests"
            . "; base-uri 'self'; font-src 'none'; frame-src 'none'; form-action 'self';$conn;");
    //ob_start(function (string $string) use ($bottom): string {return "$string$bottom\n";});
    ob_start(fn(string $string): string => "$string$bottom\n");
    $origin = null;
    $baseColor = '';
    $links = array();
    $afterTitle = '[[Unknown]]';
    if ($linkout = file_get_contents(__DIR__ . '/../sites.json')) {
        if ($linkout = json_decode($linkout, true)) {
            foreach ($linkout as $linky) {
                $alt = htmlspecialchars12($linky['title'] ?? '');
                $outline = $linky['primColor'];
                $back = $linky['backgColor'];
                if (is_string($options['siteOverride'])) {
                    $isThis = $options['siteOverride'] === $linky['href'];
                } else $isThis = $linky['this'];
                if ($isThis) {
                    $favicon = $iconPath;
                    $baseColor = "data-base-color=$outline";
                    $afterTitle = $linky['afterTitle'];
                    $origin = $linky['href'];
                } else $favicon = "{$linky['favicon']}";
                $out = ($isThis ? "data-o=$outline\x20data-b=$back" : '');
                $links[] = "<antnav-option $out><a href='{$linky['href']}'><img src='$favicon' alt"
                        . "='$alt' width={$linky['w']} height={$linky['h']}></a></antnav-option>";
            }
        }
    }
    // halloween colors: $bgColor = '#a66d01';$borderColor = '#f69b14';
    // winter colors: $bgColor = '#f0f0f0';$borderColor = '#fefefe';
        $bgColor = '#0073a6';
        $borderColor = '#00a8f3';
        if (array_key_exists('borderColor', $options)
                && array_key_exists('backColor', $options)
                && preg_match('/^(#?[a-fA-F0-9]{6}),(#?[a-fA-F0-9]{6})$/D',
                        "{$options['borderColor']},{$options['backColor']}",
                        $matches)) [, $borderColor, $bgColor] = $matches;
    $title = htmlspecialchars12("$title ($afterTitle)");
    $base = !empty($options['base']) ? "<base href=\"{$options['base']}\">" : '<!--base/-->';
    echo "<!DOCTYPE html><html lang=\"{$options['lang']}\" data-line=$borderColor data-bg=$bgColor>" .
            "<meta charset=UTF-8><title>$title</title>$base\n<script type=importmap>$importmap" .
            "</script><script type=module src=/require/JSONScript.js></script>\n"
            . "<meta name=viewport content='width=device-width,initial-scale=1'>";
    /** @noinspection PhpForeachOverSingleElementArrayLiteralInspection */
    foreach (['/require/header3/ANTStylesheet.css'] as $stylelink)
        echo "\n<link href=$stylelink rel=stylesheet>";

    $night = (int)(bool)$options['nightLightOverride'];
    /** @noinspection HtmlUnknownTarget */
    echo "\n<link href='/require/header3/nightLight.css.php?n=$night' rel=stylesheet>";

    foreach ($options['stylelinks'] as $stylelink) {
        $stylelink = htmlspecialchars12($stylelink);
        echo "\n<link href='$stylelink' rel=stylesheet>";
    }

    /** @noinspection HtmlUnknownTarget */
    echo "\n<link rel=icon href=$iconPath>";
    if (is_string($options['desc'])) {
        $desc = htmlspecialchars12($options['desc']);
        echo "\n<meta name=description content='$desc'>";
    }

    foreach ($options['metatags'] as $metatag) {
        if (is_null($metatag)) continue;
        $name = htmlspecialchars12($metatag[0]);
        $cont = htmlspecialchars12($metatag[1]);
        echo "\n<meta name='$name' content='$cont'>";
    }
    echo "<meta name=theme-color content=$bgColor>"; // $borderColor>
    if ($origin) {
        if ($canonical = getFrom($user_options, 'canonical')) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection, PhpUnhandledExceptionInspection */
            $canonical = \Uri\WhatWg\Url::parse($canonical, new \Uri\WhatWg\Url($origin))->toAsciiString();
            echo "\n<link href='$canonical' rel=canonical>";
        }
    }
    $class = '"' . htmlspecialchars12(implode("\x20", $options['class'] ?? array())) . '"';

    /** @noinspection HtmlUnknownTarget */
    echo "\n<script src=/require/header3/domContentLoadedPromise.js></script>\n<body class=$class>";
    echo "<nav class=headernav $baseColor><div>\n" . implode('', $links) . "\n</div></nav>";
    if ($linkarrays = $options['linkarrays'] ?? array(['text' => 'ANTRequest.nl', 'href' => 'https://antrequest.nl/'])) {
        echo "<nav class=breadcrumbs-list><div><ol>";
        if (!is_null($arr = array_shift($linkarrays))) {
            $text = htmlspecialchars12("{$arr['text']}");
            $href = htmlspecialchars12("{$arr['href']}");
            echo "<li><a href='$href' aria-current=page>$text</a>";
        }
        foreach ($linkarrays as $arr) {
            if (is_null($arr)) continue;
            $text = htmlspecialchars12("{$arr['text']}");
            $href = htmlspecialchars12("{$arr['href']}");
            echo "\n<li><a href='$href'>$text</a>";
        }
        echo "</ol></div></nav>";
    }
    echo "\n<!-- WebPage -->\n\n";
}

function getFrom(array $array, string|int $property, mixed $default = null): mixed
{
    return array_key_exists($property, $array) ? ($array[$property] ?? $default) : $default;
}
