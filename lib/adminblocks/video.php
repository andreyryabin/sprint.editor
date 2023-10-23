<?php

namespace Sprint\Editor\AdminBlocks;

use Sprint\Editor\Locale;
use Sprint\Editor\Tools\Coub;
use Sprint\Editor\Tools\Rutube;
use Sprint\Editor\Tools\UploadMp4;
use Sprint\Editor\Tools\Vimeo;
use Sprint\Editor\Tools\Vkontakte;
use Sprint\Editor\Tools\Youtube;

class Video
{
    /**
     * @var string
     */
    private $url = '';

    public function __construct()
    {
        $this->url = !empty($_REQUEST['url']) ? trim($_REQUEST['url']) : '';
    }

    public function execute()
    {
        $services = [
            Youtube::class,
            Vimeo::class,
            Rutube::class,
            Vkontakte::class,
            UploadMp4::class,
            Coub::class,
        ];

        $videoHtml = '';
        if (!empty($this->url)) {
            foreach ($services as $service) {
                $videoHtml = $service::getVideoHtml($this->url, 320, 180);
                if ($videoHtml) {
                    break;
                }
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode(
            Locale::convertToUtf8IfNeed(
                [
                    'url' => $this->url,
                    'html' => $videoHtml,
                ]
            )
        );
    }
}
