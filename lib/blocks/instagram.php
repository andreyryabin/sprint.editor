<?php

namespace Sprint\Editor\Blocks;

use CPHPCache;
use Sprint\Editor\Module;

class Instagram
{
    static public function getHtml($block, $params = [])
    {
        if (empty($block['url'])) {
            return '';
        }

        $ttl = 3600 * 24 * 7;

        $initdir = 'sprint_editor';
        $uniqstr = md5(serialize(['instagram_post', $block['url'], $params]));

        $obCache = new CPHPCache();
        if ($obCache->InitCache($ttl, $uniqstr, $initdir)) {
            $vars = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $endpoint = 'https://graph.facebook.com/v8.0/instagram_oembed?' . http_build_query(
                    [
                        'url'          => $block['url'],
                        'access_token' => implode(
                            '|', [
                                Module::getDbOption('instagram_app_id'),
                                Module::getDbOption('instagram_app_secret'),
                            ]
                        ),
                    ]
                );

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
