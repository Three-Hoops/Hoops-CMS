<?php

namespace App\Traits;

use HTMLPurifier;
use HTMLPurifier_Config;

trait SanitisesContent
{
    protected function sanitiseHtml(?string $html): ?string
    {
        if (blank($html)) {
            return $html;
        }

        $cachePath = storage_path('app/htmlpurifier');
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p,br,strong,em,u,s,h2,h3,h4,ul,ol,li,blockquote,' .
            'a[href|target|rel],img[src|alt|width|height],' .
            'pre,code,table,thead,tbody,tr,th,td'
        );
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);
        $config->set('Attr.AllowedRel', 'noopener noreferrer');
        $config->set('Cache.SerializerPath', $cachePath);

        return (new HTMLPurifier($config))->purify($html);
    }
}
