<?php

namespace Sprint\Editor\Blocks;

use CPHPCache;

class Twitter
{
    static public function getHtml($block, $params = [])
    {
        if (empty($block['url'])) {
            return '';
        }

        $ttl = 3600 * 24 * 7;

        $initdir = 'sprint_editor';
        $uniqstr = md5(serialize(['twitter_post', $block['url'], $params]));

        $obCache = new CPHPCache();
        if ($obCache->InitCache($ttl, $uniqstr, $initdir)) {
            $vars = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $endpoint = 'https://publish.twitter.com/oembed?' . http_build_query(['url' => $block['url']]);
            $ctx = stream_context_create(['http' => ['timeout' => 5,]]);

            $vars = file_get_contents($endpoint, false, $ctx);
            $vars = json_decode($vars, true);

            if (json_last_error() == JSON_ERROR_NONE && is_array($vars)) {
                $obCache->EndDataCache($vars);
            } else {
                $obCache->EndDataCache([]);
            }
        } else {
            $vars = [];
        }

        $vars = array_merge(['url' => $block['url'], 'html' => ''], $vars);
        return $vars['html'];
    }
}
