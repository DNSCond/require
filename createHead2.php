<?php namespace ANTHeader;

use Color;
use DateInterval;
use ContentSecurityPolicy\ContentSecurityPolicy;
use function Helpers\htmlspecialchars12;

require_once __DIR__ . "/Color.php";
require_once __DIR__ . "/helpers.php";

function create_head2(string $title, array $user_options, ?array $links = null, ?array $navOptions = null): array
{
    $options = [
            'base' => getFrom($user_options, 'base'),
            'lang' => getFrom($user_options, 'lang', 'en'),
            'v' => getFrom($user_options, 'moduleVersion', '2'),
            'desc' => getFrom($user_options, 'desc'),
            'hiddenTopBar' => getFrom($user_options, 'hiddenTopBar', false) ? ' hidden' : '',
            'defaultCSP' => getFrom($user_options, 'defaultCSP', true),
            'ventHref' => getFrom($user_options, 'ventHref', false),
            'linkarrays' => getFrom($user_options, 'bread', array()),
            'csp' => getFrom($user_options, 'csp'),
    ];
    ob_start();
    // originally 32vh
    $ventStatus_VentOn = false;
    $bottom = "<div style=height:100vh>";
    $ventHref = '/gallery/char/window';
    if (is_string($options['ventHref'])) {
        $ventStatus_VentOn = true;
        $ventHref = $options['ventHref'];
    } ?>
    <div class=bottom-divs>
    <div style='height:7vh;border-bottom:4px solid var(--primaryColor)'></div>
    <div style='height:93vh;background-color:#36393f;background-image:linear-gradient(#2b2d32 5%, #36393f 12%, #36393f 100%)'>
        <div style=padding:5vh>
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
                            echo "<rect x=$j y=80  width=80 height=640 fill='$ventColorL' $attrs/>";
                            echo "<rect x=$j y=550 width=80 height=170 fill='$ventColorD'/>\x20";
                        } ?></g>
                </svg>
            </a>
        </div>
        <script type=application/json is=output-script><?= json_encode([
                    '$options' => $options, '$user_options' => $user_options,
            ], JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
    </div>
    </div><?= "\n</div>";
    $bottom = $bottom . preg_replace('/\\s+/', ' ', ob_get_clean());
    $bottom = str_replace('> <', ">\n<", $bottom);

    ob_start(function (string $string) use ($options, $bottom): string {
        if ($options['defaultCSP']) {
            header("Content-Security-Policy: default-src 'self'; img-src 'self' blob:; script-src 'unsafe-inline'" .
                    " 'self' https://keepandroidopen.org/banner.js; style-src 'self' 'unsafe-inline'; object-src 'none';" .
                    " frame-ancestors 'none'; base-uri 'self'; upgrade-insecure-requests; font-src 'none'; frame-src 'none';");
            header("Content-Security-Policy-Report-Only: default-src 'none'; img-src 'self' blob:; script-src 'self' " .
                    "https://keepandroidopen.org/banner.js; style-src 'self' 'unsafe-inline'; frame-ancestors 'none'; " .
                    "font-src 'none'; base-uri 'self'; frame-src 'none'; form-action 'self'");
        } elseif ($options['csp'] instanceof ContentSecurityPolicy || is_array($options['csp'])) {
            if ($options['csp'] instanceof ContentSecurityPolicy) $options['csp']->send(); else {
                foreach ($options['csp'] as $csp) $csp->send();
            }
        }
        return "$string$bottom\n";
    });
    $title = htmlspecialchars12("$title (ANTRequest.nl)");
    $base = !empty($options['base']) ? "<base href=\"{$options['base']}\">" : '<!--base/-->';
    $importmap = json_encode([
            'imports' => array(
                    "Datetime_global" => "/require/head2/datetime-local-v{$options['v']}/Datetime_global.js",
                    "RelativeTimeChecker" => "/require/head2/datetime-local-v{$options['v']}/RelativeTimeChecker.js",
                    "AlternativeBuiltins" => "/require/head2/datetime-local-v{$options['v']}/AlternativeBuiltins.js",
                    "temporal-polyfill" => "/require/head2/temporal.js", "anthelpers" => "/require/head2/anthelpers.js",
            ),
    ]);
    array_unshift($links, new ANTNavMetaTag('viewport', 'width=device-width,initial-scale=1'));
    if (!empty($options['desc'])) {
        array_unshift($links, new ANTNavMetaTag('description', $options['desc']));
    }
    $nav = array();
    $bgColor = '#0073a6';
    $borderColor = '#00a8f3';
    // tabs
    if (is_array($navOptions)) {
        $selectionMade = false;
        foreach ($navOptions as $navOption) {
            if (!($navOption instanceof ANTNavOption)) {
                $class = is_null($navOption) ? "NULL" : get_class($navOption);
                throw new \TypeError("navigation options Must be ANTNavOption ($class)");
            }
            $nav[] = $navOption->toString($selectionMade ? false : null);
            if ($navOption->selected) {
                $selectionMade = true;
                $borderColor = $navOption->borderColor;
                $bgColor = $navOption->bgColor;
                array_unshift($links, new ANTNavLinkTag('icon', $navOption->getURL()));
            }
        }
    } elseif (empty($navOptions)) {
        $nav[] = $navOption = ANTNavHome(true);
        $borderColor = $navOption->borderColor;
        $bgColor = $navOption->bgColor;
        array_unshift($links, new ANTNavLinkTag('icon', $navOption->getURL()));
    }
    // $oldBorderColor=$borderColor;
    // $bgColor = '#a66d00';
    // $borderColor = '#fea700';
    echo "<!DOCTYPE html><html lang=\"{$options['lang']}\" data-p=$borderColor data-s=$bgColor>" .
            "<meta charset=UTF-8><title>$title</title>$base\n<script type=importmap>$importmap" .
            "</script><script type=module src=/require/JSONScript.js></script>\n\n";
    array_unshift($links, new ANTNavLinkTag('stylesheet', '/require/head2/ANTStylesheet.css'));
    if (is_array($links)) {
        array_unshift($links, new ANTNavMetaTag('theme-color', "$bgColor"));
        foreach ($links as $link) {
            if (!($link instanceof ANTNavLinkTag || $link instanceof ANTNavScript
                    || $link instanceof ANTNavIStyle || $link instanceof ANTNavIScript
                    || $link instanceof ANTNavMetaTag || $link instanceof ANTNavJSONScript
                    || $link instanceof ANTNavArbitraryHTML)) {
                throw new \TypeError("Links Must be 'ANTNavLink's or 'ANTNavIStyle's");
            }
            echo $link->toString();
        }
    }
    echo "\n<script src=/require/head2/domContentLoadedPromise.js></script>" .
            "<body>\n<nav class=headernav{$options['hiddenTopBar']}><div>\n" .
            implode("\n", $nav) . "\n</div></nav>";
    if ($linkarrays = $options['linkarrays']) {
        echo "<nav class=breadcrumbs-list><ol>";
        foreach ($linkarrays as $arr) {
            if (is_null($arr)) continue;
            $text = htmlspecialchars12("{$arr['text']}");
            $href = urlencode("{$arr['href']}");
            echo "<li><a href=$href>$text</a>";
        }
        echo "</ol></nav>";
    }
    //echo "\n<div id=keepandroidopen></div><script crossorigin src='https://keepandroid" .
    //        "open.org/banner.js?id=keepandroidopen&size=mini&&animation=off'></script>";
    echo "\n\n<!-- webpage-->\n\n";
    return array();
}

function getFrom(array $array, string|int $property, mixed $default = null): mixed
{
    return array_key_exists($property, $array) ? $array[$property] : $default;
}

function ANTNavHome(bool $selected = false): ANTNavOption
{
    return new ANTNavOption('https://antrequest.nl', '/dollmaker2/icon/endpoint.php?bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
            'Home', new Color('#0073a6'),
            new Color('#00a8f3'), $selected);
}

function ANTNavFavicond(string $linkTo, string $altText, bool $selected = false): ANTNavOption
{
    return new ANTNavOption("$linkTo", '/dollmaker2/icon/endpoint.php?bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
            "$altText", new Color('#0073a6'),
            new Color('#00a8f3'), $selected);
}

function ANTNavReddcond(string $linkTo, string $altText, bool $selected = false): ANTNavOption
{
    return new ANTNavOption("$linkTo", '/dollmaker2/icon/endpoint.php?preset=Reddcat', "$altText",
            new Color('a62c00'), new Color('ff4500'), $selected);
}

function ANTNavBuzz(string $linkTo, string $altText, bool $selected = false): ANTNavOption
{
    return new ANTNavOption("$linkTo", '/dollmaker2/icon/endpoint.php?bgcolor=%23fff100&fgcolor=%238cfffa' .
            '&L=%23fff200&W=%23000000&LC=%2300a8f3&RC=%23ff4500&accessory=mouth+Left_Light+RightLight+Middle+stripes',
            "$altText", new Color('a68300'), new Color('fff100'), $selected);
}

function ANTNavBinary(string $linkTo, string $altText, bool $selected = false): ANTNavOption
{
    return new ANTNavOption("$linkTo", '/dollmaker2/icon/endpoint.php?preset=Binary', "$altText",
            new Color('00a600'), new Color('00ff00'), $selected);
}

class ANTNavOption
{
    private readonly string $linkTo;
    private readonly string $imageTo;
    private readonly string $altText;
    public Color $bgColor;
    public Color $borderColor;

    /**
     * creates an navigation option
     *
     * @param string $linkTo where it links to
     * @param string $imageTo /dollmaker2/icon/ image url
     * @param string $altText the image's alt text
     * @param Color|string $bgColor the color of the background
     * @param Color|string $borderColor the color of the borders
     * @param bool $selected if its the current selected tab
     */
    public function __construct(
            string       $linkTo,
            string       $imageTo,
            string       $altText,
            Color|string $bgColor,
            Color|string $borderColor,
            public bool  $selected = false)
    {
        $this->bgColor = new Color($bgColor);
        $this->borderColor = new Color($borderColor);
        $this->linkTo = (htmlspecialchars12($linkTo));
        $this->imageTo = htmlspecialchars12($imageTo);
        $this->altText = htmlspecialchars12($altText);
    }

    public function toString(?bool $selected = null): string
    {
        $ariaCurrent = 'aria-current="false"';
        $borderColor = $this->borderColor;
        $bgColor = $this->bgColor;
        if (!$this->selected) {
            $borderColor = $bgColor = 'currentColor';
        } else {
            $ariaCurrent = 'aria-current="page"';
        }
        if (!is_null($selected) && ($selected !== !$this->selected)) {
            if ($selected === true) {
                $borderColor = $this->borderColor;
                $bgColor = $this->bgColor;
            } elseif ($selected === false) {
                $borderColor = $bgColor = 'currentColor';
            }
        }
        return "<antnav-option style=border-color:$borderColor;background-color:$bgColor;" .
                "><a href=\"$this->linkTo\" title=\"$this->altText\" $ariaCurrent><img src=\"" .
                "$this->imageTo\" width=512 height=512 alt=\"$this->altText\"></a></antnav-option>";
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getURL(): string
    {
        return $this->imageTo;
    }
}

readonly class ANTNavLinkTag
{
    public function __construct(private string $rel, private string|array $href)
    {
    }

    public function toString(): string
    {
        $result = [];
        if (is_array($this->href)) {
            foreach ($this->href as $item) {
                $result[] = "<link rel=\"$this->rel\" href=\"$item\"/>";
            }
        } else {
            $result[] = "<link rel=\"$this->rel\" href=\"$this->href\"/>";
        }
        return implode("\n", $result) . "\n";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

readonly class ANTNavMetaTag
{
    public function __construct(private string $name, private string $content)
    {
    }

    public function toString(): string
    {
        $name = htmlspecialchars12($this->name);
        $content = htmlspecialchars12($this->content);
        return "<meta name=\"$name\" content=\"$content\">";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

readonly class ANTNavIStyle
{
    public function __construct(private string $style)
    {
    }

    public function toString(): string
    {

        return "<style>$this->style</style>";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

readonly class ANTNavIScript
{
    public function __construct(private string $script)
    {
    }

    public function toString(): string
    {

        return "<script>$this->script</script>";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

readonly class ANTNavScript
{
    public function __construct(private string $link, private bool $module = false)
    {
    }

    public function toString(): string
    {
        $module = ($this->module ? 'type=module' : 'data-type=module');
        return "<script src=\"$this->link\" $module></script>";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

readonly class ANTNavJSONScript
{
    private string $json;
    private string $type;
    private ?string $class;
    private ?string $id;

    public function __construct(mixed $jsonData, string $type = 'application/json', null|string|array $class = null, ?string $id = null)
    {
        $htmlFlags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5;
        $this->json = json_encode($jsonData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $this->id = is_null($id) ? null : htmlspecialchars($id, $htmlFlags, 'UTF-8');
        $this->class = is_null($class) ? null : htmlspecialchars(is_string($class) ? $class : implode(' ', $class), $htmlFlags, 'UTF-8');
        if (preg_match('/^[a-z0-9\\-_]+\\/[a-z0-9\\-_]+$/D', $type)) {
            $this->type = $type;
        } else {
            $this->type = 'application/json';
        }
    }

    public function toString(): string
    {
        $attrs = $this->type;
        if (!is_null($this->class)) $attrs .= "\x20class=\"$this->class\"";
        if (!is_null($this->id)) $attrs .= "\x20id=\"$this->id\"";
        return "<script type=$attrs>$this->json</script>";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

readonly class ANTNavArbitraryHTML
{
    public function __construct(private string $tagNameComment, private string $html)
    {
    }

    public function toString(): string
    {
        return "<!-- $this->tagNameComment -->$this->html<!-- /$this->tagNameComment -->";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

function set_cookie(string $name, ?string $value, array $options, bool $send = true): bool|string
{
    $name = urlencode($name);
    $value = is_string($value) ? urlencode($value) : null;
    if (empty($name)) {
        return false; // Name must not be empty
    }

    // Determine if the connection is secure
    $secure = !empty($_SERVER['HTTPS']) ? 'Secure' : '';

    // Set the domain
    $domain = "Domain={$_SERVER['SERVER_NAME']}";

    // Validate the path

    if (array_key_exists('path', $options) && is_string($options['path'])) {
        $path = preg_match('/^[\\/%a-zA-Z\\-0-9._]+$/D', $options['path']) ? "Path={$options['path']}" : '';
    } else {
        $path = 'Path=/';
    }

    // Max-Age handling
    $date = new \DateTimeImmutable("@{$_SERVER['REQUEST_TIME']}");
    if (array_key_exists('max-age', $options) && is_integer($maxAge = $options['max-age'])) {
        $expires = $date->add(new DateInterval("PT{$maxAge}S"));
        if ($maxAge > 0) {
            $maxAge = "Max-Age=$maxAge";
        } else {
            $maxAge = '';
        }
        $expires = "Expires={$expires->format('D, d M Y H:i:s \\G\\M\\T')}";
    } else {
        $expires = $maxAge = '';
    }
    if (array_key_exists('session', $options) && $options['session']) {
        $expires = $maxAge = '';
    }

    // HttpOnly flag
    $httpOnly = array_key_exists('HttpOnly', $options) && $options['HttpOnly'] ? 'HttpOnly' : '';

    if (empty($value)) {
        $maxAge = "Max-Age=0";
        $expires = gmdate('D, d M Y H:i:s', +"{$_SERVER['REQUEST_TIME']}" - 100) . " GMT";
    }

    $header = '';
    foreach ([$maxAge, $expires, $domain, $httpOnly, $path, $secure, 'SameSite=Lax'] as $item) {
        if (empty($item)) continue;
        $header .= "; $item";
    }
    // Assemble the Set-Cookie header
    $header = "Set-Cookie: $name=$value$header";

    // Send the cookie header
    if ($send) header($header, false);
    return $header;
}
