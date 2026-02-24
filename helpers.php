<?php namespace Helpers;
function sha256(string $string): string
{
    return hash('sha256', "$string");
}

function cbyte($num): string
{
    $x = array("bytes", "KB", "MB", "GB", "TB");
    $i = 0;
    while ($num >= 1024) {
        $num = $num / 1024;
        if (is_null($x[++$i])) {
            $i--;
            break;
        }
    }
    return round($num, 4) . " $x[$i]";
}

function htmlspecialchars12(string $value): string
{
    $html = str_replace('"', '&quot;',
        str_replace('>', '&gt;',
            str_replace('<', '&lt;',
                str_replace('\'', '&#39;',
                    str_replace('&', '&amp;',
                        "$value")))));
    return ($html);
}

function json_fromArray(mixed $json, bool|int $JSON_PRETTY_PRINT = true): false|string
{
    $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if (is_int($JSON_PRETTY_PRINT) && $JSON_PRETTY_PRINT >= 0) {
        $options |= JSON_PRETTY_PRINT;
        $json = json_encode($json, $options);
        return preg_replace_callback('/^ +/m', (function (array $matches)
        use ($JSON_PRETTY_PRINT): string {
            return str_repeat(' ', (strlen($matches[0]) / 4) * $JSON_PRETTY_PRINT);
        }), $json);
    } elseif (is_bool($JSON_PRETTY_PRINT) && $JSON_PRETTY_PRINT) {
        $options |= JSON_PRETTY_PRINT;
    }
    return json_encode($json, $options);
}

function normalize_newlines(string $string): string
{
    return str_replace("\r", "\n", str_replace("\r\n", "\n", "$string"));
}

namespace Helpers\Mime;

function sort_mimetypes(): array
{
    if (!array_key_exists('HTTP_ACCEPT', $_SERVER)) {
        return array();
    }
    $acceptableTypes = array_map('trim', explode(',', strtolower("{$_SERVER['HTTP_ACCEPT']}")));
    usort($acceptableTypes, function ($a, $b) use ($acceptableTypes) {
        $qA = getQualityFactor($a, $acceptableTypes);
        $qB = getQualityFactor($b, $acceptableTypes);
        return $qB <=> $qA;
    });
    return array_map(function ($item) {
        return preg_replace('/;.+/', '', "$item");
    }, $acceptableTypes);
}

function get_accept_mimetype(array $mimeTypes, bool $Strict_exact_match = false): ?string
{
    // Parse the Accept header into an array of acceptable mime types
    $acceptableTypes = sort_mimetypes();
    $mimeTypes = array_map('strtolower', $mimeTypes);
    // Sort the mime types array based on the "q" parameter (quality factor)
    usort($mimeTypes, function ($a, $b) use ($acceptableTypes) {
        $qA = getQualityFactor($a, $acceptableTypes);
        $qB = getQualityFactor($b, $acceptableTypes);

        // Compare quality factors (higher is better)
        return $qB <=> $qA;
    });
    // Check if any of the acceptable mime types match the provided array
    $image_ = null;
    $image_AnyAllowed = false;
    foreach ($acceptableTypes as $acceptableType) {
        if (in_array($acceptableType, $mimeTypes)) {
            return $acceptableType;
        } else if ($acceptableType === '*/*' && !$Strict_exact_match) {
            return $mimeTypes[0];
        }
        if (!$Strict_exact_match)
            if ($image_ === null && preg_match('/^image\\/(?!\\*)/', $acceptableType)) {
                $image_ = $acceptableType;
            } elseif (str_starts_with($acceptableType, 'image/*')) {
                $image_AnyAllowed = true;
            }
    }
    if ($image_AnyAllowed && $image_ !== null && !$Strict_exact_match) {
        return $image_;
    }
    // No acceptable mime type found
    return null;
}

function getQualityFactor(string $mime, array $acceptableTypes): float
{
    // Extract the "q" parameter from the mime type (if present)
    preg_match('/;q=([0-9.]+)/', $mime, $matches);
    $q = isset($matches[1]) ? (float)$matches[1] : 1.0;

    // Check if the mime type is in the acceptable types array
    return in_array($mime, $acceptableTypes) ? $q : 0.0;
}
