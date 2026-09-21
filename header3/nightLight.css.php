<?php header('content-type: text/css');
$nightLightOverride = (array_key_exists('n', $_GET) && !!"{$_GET['n']}");
ob_start(fn(string $string): string => (
     $nightLightOverride ? "$string" : preg_replace('/\\s+/', ' ',
         "@media (prefers-color-scheme: dark) {{$string}}"))) ?>
:root {
    --primaryColor: #36393f;
    --secondaryColor: #36393f;
    --textColor: #ffffff;
    --backColor: #171717;
}

a:visited, a:link {
    color: #94DDFF;
}

a:hover {
    color: orangered;
}

a:active {
    color: white;
}

:root {
    --bgColor: #000000;
    --secondaryColor: #000000;
}
