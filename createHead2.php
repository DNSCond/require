<?php namespace ANTHeader;

use Color;
use function Helpers\htmlspecialchars12;
use function Helpers\json_fromArray;

require_once __DIR__ . "/Color.php";
require_once __DIR__ . "/helpers.php";

function create_head2(string $title, array $user_options, ?array $links = null, ?array $navOptions = null): array
{
    $options = [
        'lang' => getFrom($user_options, 'lang', 'en'),
        'base' => getFrom($user_options, 'base', '/'),
        'v' => getFrom($user_options, 'moduleVersion', '2'),
        'desc' => getFrom($user_options, 'desc'),
        'hiddenTopBar' => getFrom($user_options, 'hiddenTopBar', false) ? ' hidden' : '',
    ];
    ob_start(function (string $string): string {
        return "$string<div style=height:32vh></div>\n";
    });
    $title = htmlspecialchars12("$title (ANTRequest.nl)");
    $base = !empty($options['base']) ? "<base href=\"{$options['base']}\">" : '<!--base/-->';
    $importmap = json_fromArray([
        'imports' => array(
            "Datetime_global" => "/require/head2/datetime-local-v{$options['v']}/Datetime_global.js",
            "RelativeTimeChecker" => "/require/head2/datetime-local-v{$options['v']}/RelativeTimeChecker.js",
            "temporal-polyfill" => "/require/head2/temporal.js",
        ),
    ], false);
    array_unshift($links, new ANTNavMetaTag(
            'viewport', 'width=device-width, initial-scale=1')
    );
    if (!empty($options['desc'])) {
        array_unshift($links, new ANTNavMetaTag('description', $options['desc']));
    }
    echo "<!DOCTYPE html><html lang=\"{$options['lang']}\"><meta charset=UTF-8><title>$title</title>$base\n";
    echo "\n";

    $nav = [];
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
    //
    $naviGatorBarColor = new Color('424242');
    $oldBorderColor = $borderColor;
    // $bgColor = '#a66d00';
    // $borderColor = '#fea700';
    array_unshift($links, new ANTNavLinkTag('stylesheet', '/require/head2/ANTStylesheet.css'));
    if (is_array($links)) {
        array_unshift($links, new ANTNavMetaTag('theme-color', "$bgColor"));
        foreach ($links as $link) {
            if (!($link instanceof ANTNavLinkTag || $link instanceof ANTNavScript
                || $link instanceof ANTNavIStyle || $link instanceof ANTNavIScript
                || $link instanceof ANTNavMetaTag)) {
                throw new \TypeError("Links Must be 'ANTNavLink's or 'ANTNavIStyle's");
            }
            echo $link->toString();
        }
    }
    echo new ANTNavIStyle(":root{--primaryColor:$borderColor;--secondaryColor:$bgColor;}")->toString();
    $n = "\n";
    echo "\n\n";
    /** @noinspection JSUnresolvedLibraryURL */
    echo "<script src=https://cdn.jsdelivr.net/npm/temporal-polyfill@0.3.0/global.min.js></script>\n";
    echo "<script src=/require/head2/domContentLoadedPromise.js></script>\n<script type=importmap>$importmap"
        . "</script>$n<script type=module src=/require/head2/import-v{$options['v']}.js></script>";
    echo "</head><body>\n<nav style=\"color:$naviGatorBarColor;background-color:currentColor;box-sizing:content-box;height:" .
        "calc(4.4em + 4px);border-bottom: 4px solid $oldBorderColor\"{$options['hiddenTopBar']}><div style=\"display:flex;"
        . "flex-wrap:nowrap;align-items:center;flex-direction:row;height:100%;position:relative;margin:auto;width:88%;"
        . "box-sizing:border-box;overflow:auto hidden\">$n" . implode("$n", $nav) . "$n</div></nav>";
    echo "\n\n<!-- webpage-->\n\n";//require/head2/import.json
    return array();
}

function getFrom(array $array, string|int $property, mixed $default = null): mixed
{
    return array_key_exists($property, $array) ? $array[$property] : $default;
}

function ANTNavHome(bool $selected = false): ANTNavOption
{
    return new ANTNavOption('https://antrequest.com', '/dollmaker2/icon/endpoint.php?bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
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
    return new ANTNavOption("$linkTo", '/dollmaker2/icon/endpoint.php?bgcolor=%23fff100&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%2300a8f3&RC=%23ff4500&accessory=mouth+Left_Light+RightLight+Middle+stripes',
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
        return "<div style=\"border-width:4px;border-style:solid;padding:0.2em;width:4em;height:4em;" .
            "border-color:$borderColor;border-bottom:none;background-color:$bgColor;\"" .
            "><a href=\"$this->linkTo\" title=\"$this->altText\" $ariaCurrent><img" .
            " src=\"$this->imageTo\" style=\"width:4em;height:4em;\" " .
            "width=512 height=512 alt=\"$this->altText\"></a></div>";
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
    public function __construct(private string $link)
    {
    }

    public function toString(): string
    {

        return "<script src=\"$this->link\"></script>";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

function sha256Base64(string $string): string
{
    return base64_encode(hash('sha256', $string, true));
}
