<?php
namespace Sprint\Editor\Blocks;

class Twitter
{

    static public function getHtml($block, $params = array()){
        if (empty($block['url'])){
            return '';
        }

        $ttl = 3600 * 24 * 7;

        $initdir = 'sprint_editor';
        $uniqstr = md5(serialize(array('twitter_post', $block['url'], $params)));

        $obCache = new \CPHPCache();
        if ($obCache->InitCache($ttl, $uniqstr, $initdir)) {
            $vars = $obCache->GetVars();

        } elseif ($obCache->StartDataCache()) {
            $endpoint = 'https://publish.twitter.com/oembed?' . http_build_query(array('url' => $block['url']));
            $ctx = stream_context_create(array('http' => array('timeout' => 5,)));

            $vars = file_get_contents($endpoint, false, $ctx);
            $vars = json_decode($vars, true);

            if (json_last_error() == JSON_ERROR_NONE && is_array($vars)) {
                $obCache->EndDataCache($vars);
            } else {
                $obCache->EndDataCache(array());
            }
        } else {
            $vars = array();
        }

        $vars = array_merge(array('url' => $block['url'], 'html' => ''), $vars);
        return $vars['html'];
    }
}