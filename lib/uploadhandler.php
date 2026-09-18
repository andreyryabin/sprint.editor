<?php

/*
 * jQuery File Upload Plugin PHP Class
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 *
 * Добавлены методы для сохранения в бд битрикса
 *
 */

namespace Sprint\Editor;

use CFile;
use Sprint\Editor\Tools\Image;
use Sprint\Editor\Tools\Youtube;
use stdClass;

class UploadHandler
{
    protected $options;
    protected $error_messages = [
        1                     => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2                     => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3                     => 'The uploaded file was only partially uploaded',
        4                     => 'No file was uploaded',
        6                     => 'Missing a temporary folder',
        7                     => 'Failed to write file to disk',
        8                     => 'A PHP extension stopped the file upload',
        'post_max_size'       => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size'       => 'File is too big',
        'min_file_size'       => 'File is too small',
        'accept_file_types'   => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'abort'               => 'File upload aborted',

    ];
    protected $bitrix_errors = [];

    function __construct($options = null, $initialize = true, $error_messages = null)
    {
        $dir = '/upload/sprint.editor/temp/';
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $dir)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . $dir, BX_DIR_PERMISSIONS, true);
        }

        $this->options = [
            'script_url'    => $this->get_full_url() . '/' . basename($this->get_server_var('SCRIPT_NAME')),
            'upload_dir'    => $_SERVER['DOCUMENT_ROOT'] . $dir,
            'upload_url'    => $dir,
            'input_stream'  => 'php://input',
            'user_dirs'     => false,
            'param_name'    => 'file',
            'max_file_size' => 100 * 1024 * 1024,
            'min_file_size' => 1,

            'mkdir_mode'          => BX_DIR_PERMISSIONS,
            'max_number_of_files' => 100,

            // Set the following option to 'POST', if your server does not support

            'access_control_allow_origin'      => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods'     => [
                'OPTIONS',
                'HEAD',
                'POST',
                'PUT',
                'PATCH',
            ],
            'access_control_allow_headers'     => [
                'Content-Type',
                'Content-Range',
                'Content-Disposition',
            ],
            // By default, allow redirects to the referer protocol+host:
            'redirect_allow_target'            => '/^' . preg_quote(
                    parse_url($this->get_server_var('HTTP_REFERER'), PHP_URL_SCHEME)
                    . '://'
                    . parse_url($this->get_server_var('HTTP_REFERER'), PHP_URL_HOST)
                    . '/', // Trailing slash to not match subdomains by mistake
                    '/' // preg_quote delimiter param
                ) . '/',

            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size'              => 10 * 1024 * 1024, // 10 MiB
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types'                => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'discard_aborted_uploads'          => true,

            // Ограничения для загрузки удалённых ресурсов по URL (SSRF-защита).
            // resource_allowed_hosts: пустой массив — разрешены любые внешние
            // https-хосты, все резолвящиеся IP которых публичные. Непустой массив
            // включает строгий allow-list хостов (точное совпадение или поддомен).
            'resource_allowed_hosts'           => [],
            'resource_max_file_size'           => 100 * 1024 * 1024,
            'resource_connect_timeout'         => 5,
            'resource_timeout'                 => 20,
        ];
        if ($options) {
            $this->options = $options + $this->options;
        }
        if ($error_messages) {
            $this->error_messages = $error_messages + $this->error_messages;
        }
        if ($initialize) {
            $this->initialize();
        }
    }

    protected function initialize()
    {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function get_full_url()
    {
        $https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0
            || !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
        return
            ($https ? 'https://' : 'http://') .
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] . '@' : '') .
            (isset($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST']
                : ($_SERVER['SERVER_NAME'] .
                    ($https && $_SERVER['SERVER_PORT'] === 443
                    || $_SERVER['SERVER_PORT'] === 80 ? '' : ':' . $_SERVER['SERVER_PORT']))) .
            substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id()
    {
        @session_start();
        return session_id();
    }

    protected function get_user_path()
    {
        if ($this->options['user_dirs']) {
            return $this->get_user_id() . '/';
        }
        return '';
    }

    protected function get_upload_path($file_name = null)
    {
        $file_name = $file_name ? $file_name : '';
        return $this->options['upload_dir'] . $this->get_user_path() . $file_name;
    }

    protected function get_query_separator($url)
    {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function get_download_url($file_name)
    {
        return $this->options['upload_url'] . $this->get_user_path() . rawurlencode($file_name);
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    protected function get_file_size($file_path, $clear_stat_cache = false)
    {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return $this->fix_integer_overflow(filesize($file_path));
    }

    protected function is_valid_file_object($file_name)
    {
        $file_path = $this->get_upload_path($file_name);
        if (is_file($file_path) && $file_name[0] !== '.') {
            return true;
        }
        return false;
    }

    protected function get_file_object($file_name)
    {
        if ($this->is_valid_file_object($file_name)) {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = $this->get_file_size(
                $this->get_upload_path($file_name)
            );
            $file->url = $this->get_download_url($file->name);
            return $file;
        }
        return null;
    }

    protected function get_file_objects($iteration_method = 'get_file_object')
    {
        $upload_dir = $this->get_upload_path();
        if (!is_dir($upload_dir)) {
            return [];
        }
        return array_values(
            array_filter(
                array_map(
                    [$this, $iteration_method],
                    scandir($upload_dir)
                )
            )
        );
    }

    protected function count_file_objects()
    {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    protected function get_error_message($error)
    {
        return isset($this->error_messages[$error]) ?
            $this->error_messages[$error] : $error;
    }

    protected function get_config_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    protected function validate($uploaded_file, $file, $error, $index)
    {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(
            (int)$this->get_server_var('CONTENT_LENGTH')
        );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size']
            && (
                $file_size > $this->options['max_file_size']
                || $file->size > $this->options['max_file_size'])
        ) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size']
            && $file_size < $this->options['min_file_size']
        ) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files'])
            && ($this->count_file_objects() >= $this->options['max_number_of_files'])
            && // Ignore additional chunks of existing files:
            !is_file($this->get_upload_path($file->name))
        ) {
            $file->error = $this->get_error_message('max_number_of_files');
            return false;
        }

        return true;
    }

    protected function upcount_name_callback($matches)
    {
        $index = isset($matches[1]) ? ((int)$matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return '(' . $index . ')' . $ext;
    }

    protected function upcount_name($name)
    {
        return preg_replace_callback(
            '/(?:(?:\(([\d]+)\))?(\.[^.]+))?$/',
            [$this, 'upcount_name_callback'],
            $name,
            1
        );
    }

    protected function get_file_name($name, $content_range)
    {
        $name = preg_replace('#[/\\\\]#', '', basename($name));

        // Keep an existing filename if this is part of a chunked upload:
        if ($content_range) {
            $uploaded_bytes = $this->fix_integer_overflow((int)$content_range[1]);
            while (is_file($this->get_upload_path($name))) {
                if ($uploaded_bytes === $this->get_file_size(
                        $this->get_upload_path($name)
                    )
                ) {
                    break;
                }
            }
        }
        return $name;
    }

    protected function handle_file_upload(
        $uploaded_file,
        $name,
        $size,
        $type,
        $error,
        $index = null,
        $content_range = null
    )
    {
        $file = new stdClass();

        $file->name = $this->get_file_name($name, $content_range);

        $file->size = $this->fix_integer_overflow((int)$size);
        $file->type = $type;
        if ($this->validate($uploaded_file, $file, $error, $index)) {
            $this->handle_form_data($file, $index);
            $upload_dir = $this->get_upload_path();
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, $this->options['mkdir_mode'], true);
            }
            $file_path = $this->get_upload_path($file->name);
            $file->path = $file_path;

            $append_file = $content_range && is_file($file_path)
                && $file->size > $this->get_file_size($file_path);
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen($this->options['input_stream'], 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = $this->get_file_size($file_path, $append_file);
            if ($file_size === $file->size) {
                $file->url = $this->get_download_url($file->name);
            } else {
                $file->size = $file_size;
                if (!$content_range && $this->options['discard_aborted_uploads']) {
                    unlink($file_path);
                    $file->error = $this->get_error_message('abort');
                }
            }
        }
        return $file;
    }

    protected function readfile($file_path)
    {
        $file_size = $this->get_file_size($file_path);
        $chunk_size = $this->options['readfile_chunk_size'];
        if ($chunk_size && $file_size > $chunk_size) {
            $handle = fopen($file_path, 'rb');
            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                @ob_flush();
                @flush();
            }
            fclose($handle);
            return $file_size;
        }
        return readfile($file_path);
    }

    protected function header($str)
    {
        header($str);
    }

    protected function get_upload_data($id)
    {
        return @$_FILES[$id];
    }

    protected function get_server_var($id)
    {
        return $_SERVER[$id] ?? '';
    }

    protected function handle_form_data($file, $index)
    {
        // Handle form data, e.g. $_POST['description'][$index]
    }

    protected function send_content_type_header()
    {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers()
    {
        $this->header('Access-Control-Allow-Origin: ' . $this->options['access_control_allow_origin']);
        $this->header(
            'Access-Control-Allow-Credentials: '
            . ($this->options['access_control_allow_credentials'] ? 'true' : 'false')
        );
        $this->header(
            'Access-Control-Allow-Methods: '
            . implode(', ', $this->options['access_control_allow_methods'])
        );
        $this->header(
            'Access-Control-Allow-Headers: '
            . implode(', ', $this->options['access_control_allow_headers'])
        );
    }

    protected function generate_response($content)
    {
        $this->head();
        if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
            $files = $content[$this->options['param_name']] ?? null;
            if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                $this->header(
                    'Range: 0-' . (
                        $this->fix_integer_overflow((int)$files[0]->size) - 1
                    )
                );
            }
        }

        $content['errors'] = implode(PHP_EOL, $this->bitrix_errors);

        echo json_encode($content);
    }

    protected function head()
    {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    protected function post()
    {
        $upload = $this->get_upload_data($this->options['param_name']);
        // Parse the Content-Disposition header, if available:
        $content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
        $file_name = $content_disposition_header ?
            rawurldecode(
                preg_replace(
                    '/(^[^"]+")|("$)/',
                    '',
                    $content_disposition_header
                )
            ) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
        $content_range = $content_range_header ?
            preg_split('/[^0-9]+/', $content_range_header) : null;
        $size = $content_range ? $content_range[3] : null;
        $files = [];
        if ($upload) {
            if (is_array($upload['tmp_name'])) {
                // param_name is an array identifier like "files[]",
                // $upload is a multi-dimensional array:
                foreach ($upload['tmp_name'] as $index => $value) {
                    $files[] = $this->handle_file_upload(
                        $upload['tmp_name'][$index],
                        $file_name ?: $upload['name'][$index],
                        $size ?: $upload['size'][$index],
                        $upload['type'][$index],
                        $upload['error'][$index],
                        $index,
                        $content_range
                    );
                }
            } else {
                // param_name is a single object identifier like "file",
                // $upload is a one-dimensional array:
                $files[] = $this->handle_file_upload(
                    $upload['tmp_name'] ?? null,
                    $file_name ?: ($upload['name'] ?? null),
                    $size ?: ($upload['size'] ?? $this->get_server_var('CONTENT_LENGTH')),
                    $upload['type'] ?? $this->get_server_var('CONTENT_TYPE'),
                    $upload['error'] ?? null,
                    null,
                    $content_range
                );
            }
        }

        $files = $this->bitrixSaveCollection($files);

        $this->generate_response(
            [$this->options['param_name'] => $files],
        );
    }

    //bitrix section

    protected function bitrixSaveOne($url)
    {
        $file = new stdClass();
        $file->path = $url;

        $files = $this->bitrixSaveCollection([$file]);

        return $files[0] ?? false;
    }

    protected function bitrixSaveCollection($files)
    {
        $res = [];
        $this->bitrix_errors = [];

        foreach ($files as $file) {
            $aFile = CFile::MakeFileArray($file->path);
            $aFile['MODULE_ID'] = 'sprint.editor';

            $checkErr = $this->checkFileAndReturnError($aFile);
            if ($checkErr) {
                $this->bitrix_errors[] = $checkErr;
                unlink($file->path);
                continue;
            }

            $bitrixId = CFile::SaveFile($aFile, 'sprint.editor');
            if (!$bitrixId) {
                $this->bitrix_errors[] = 'Ошибка загрузки файла';
                unlink($file->path);
                continue;
            }

            if (!empty($this->options['bitrix_resize'])) {
                $res[] = Image::resizeImage2($bitrixId, $this->options['bitrix_resize']);
            } else {
                $res[] = CFile::GetFileArray($bitrixId);
            }

            unlink($file->path);
        }
        return $res;
    }


    public function checkFileAndReturnError(array $aFile): string
    {
        if ($aFile['type'] == 'image/svg+xml' || $aFile['type'] == 'image/svg') {
            $file = new \Bitrix\Main\IO\File($aFile['tmp_name']);
            $ok = $this->checkSvgFile($file->getContents());

            return $ok ? '' : 'SVG-файл содержит опасное содержимое';
        }

        if (\Bitrix\Main\Web\MimeType::isImage($aFile['type'])) {
            return (string)CFile::CheckImageFile($aFile);
        }

        return (string)CFile::CheckFile($aFile);
    }


    /**
     * Сохраняет удалённый ресурс по URL.
     *
     * URL не передаётся в CFile::MakeFileArray() напрямую: этот вызов скачивает
     * содержимое без контроля редиректов, повторного резолва DNS, диапазонов IP
     * и размера ответа, что даёт SSRF. Вместо этого ресурс безопасно скачивается
     * во временный локальный файл (см. downloadRemoteResource()) и уже локальный
     * путь передаётся в Bitrix API.
     */
    public function saveResource($url)
    {
        $url = trim((string)$url);

        if (!empty($this->options['bitrix_resize'])) {
            $imgurl = Youtube::getPreviewImg($url);
            if ($imgurl) {
                $this->generate_response(
                    [
                        'image' => $this->bitrixSaveOne($this->downloadRemoteResource($imgurl)),
                        'video' => $url,
                    ]
                );
            } else {
                $this->generate_response(
                    [
                        'image' => $this->bitrixSaveOne($this->downloadRemoteResource($url)),
                    ]
                );
            }
        } else {
            $this->generate_response(
                [
                    'file' => $this->bitrixSaveOne($this->downloadRemoteResource($url)),
                ]
            );
        }
    }

    /**
     * Безопасно скачивает удалённый ресурс и возвращает путь к локальному
     * временному файлу (или прерывает выполнение с HTTP 400).
     *
     * Политика: только https, без учётных данных в URL, опциональный allow-list
     * хостов, все резолвящиеся A/AAAA-адреса должны быть публичными, соединение
     * выполняется строго с проверенным IP (DNS-pinning), редиректы запрещены,
     * размер ответа ограничен.
     *
     * @param string $url
     * @return string
     */
    protected function downloadRemoteResource(string $url): string
    {
        $url = trim((string)$url);

        // Локальный путь от корня сайта (/upload/logo.jpg, /local/img.png и т.п.):
        // копируем существующий файл во временную директорию (вне web-доступа)
        // и дальше работаем с локальным файлом. Протоколо-относительные URL
        // ('//host/path') отсекаются и обрабатываются как удалённые ресурсы.
        if (preg_match('#^/(?!/)#', $url)) {
            return $this->copyLocalResource($url);
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            $this->abortResource('Invalid url');
        }

        if (strtolower($parts['scheme']) !== 'https') {
            $this->abortResource('Only HTTPS allowed');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            $this->abortResource('Credentials in url are not allowed');
        }

        $host = rtrim($parts['host'], '.');
        if ($host === '' || !preg_match('/^[a-zA-Z0-9\.\-_\[\]:]+$/', $host)) {
            $this->abortResource('Invalid url host');
        }

        if (!$this->isAllowedResourceHost($host)) {
            $this->abortResource('Host not allowed');
        }

        // Проверяем ВСЕ адреса хоста: если хотя бы один внутренний — отказ.
        $ips = $this->resolveResourceHost($host);
        if (!$ips) {
            $this->abortResource('Unable to resolve host');
        }
        foreach ($ips as $ip) {
            if (!$this->isPublicAddress($ip)) {
                $this->abortResource('Access to internal resources denied');
            }
        }

        $port = (int)($parts['port'] ?? 0);
        if ($port <= 0) {
            $port = 443;
        }

        $maxSize = (int)$this->options['resource_max_file_size'];
        $target = $this->getRemoteTempPath($url);

        $downloaded = $this->downloadWithStream($url, $host, $port, $ips[0], $target);

        $size = is_file($target) ? $this->get_file_size($target) : 0;
        if (!$downloaded || $size < 1) {
            if (is_file($target)) {
                unlink($target);
            }
            if ($maxSize > 0 && $size > $maxSize) {
                $this->abortResource('File is too big');
            }
            $this->abortResource('Unable to download file');
        }

        if ($maxSize > 0 && $size > $maxSize) {
            unlink($target);
            $this->abortResource('File is too big');
        }

        return $target;
    }

    protected function abortResource($message)
    {
        http_response_code(400);
        die($message);
    }

    /**
     * Копирует локальный файл из корня сайта во временную директорию
     * и возвращает путь к копии. Путь ограничен DOCUMENT_ROOT,
     * traversal ('/../') запрещён.
     *
     * @param string $url путь вида /upload/logo.jpg или /local/img.png
     * @return string
     */
    protected function copyLocalResource(string $url): string
    {
        // Допустимые символы (без ../, \0 и query).
        if (preg_match('#[\x00]#', $url) || preg_match('#(^|/)\\.\\.?(/|$)#', urldecode($url))) {
            $this->abortResource('Invalid local path');
        }

        $root = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/\\');
        if ($root === '') {
            $this->abortResource('Invalid document root');
        }

        $source = $root . '/' . ltrim(str_replace('\\', '/', $url), '/');
        $real = realpath($source);
        $realRoot = realpath($root);

        if (!$real || !$realRoot || strpos($real, $realRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) {
            $this->abortResource('Local file not found');
        }

        $maxSize = (int)$this->options['resource_max_file_size'];
        $size = (int)@filesize($real);
        if ($maxSize > 0 && $size > $maxSize) {
            $this->abortResource('File is too big');
        }

        $target = $this->getRemoteTempPath($url);
        if (!@copy($real, $target)) {
            $this->abortResource('Unable to copy file');
        }

        return $target;
    }

    /**
     * Проверяет хост по allow-list (если он задан в опциях).
     *
     * @param string $host
     * @return bool
     */
    protected function isAllowedResourceHost($host)
    {
        $allowed = $this->options['resource_allowed_hosts'];
        if (empty($allowed) || !is_array($allowed)) {
            // Allow-list не задан: разрешены любые внешние https-хосты,
            // прошедшие проверку всех резолвящихся IP-адресов.
            return true;
        }

        $host = strtolower($host);
        foreach ($allowed as $item) {
            $item = strtolower(trim((string)$item, ". \t\n\r\0\x0B"));
            if ($item === '') {
                continue;
            }
            if ($host === $item || substr($host, -strlen('.' . $item)) === '.' . $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Резолвит хост во все A/AAAA-адреса.
     *
     * @param string $host
     * @return string[]
     */
    protected function resolveResourceHost($host)
    {
        $host = trim($host, '[]'); // IPv6-литерал в URL: https://[::1]/
        $ips = [];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            if (function_exists('dns_get_record')) {
                $records = @dns_get_record($host, DNS_A | DNS_AAAA);
                if (is_array($records)) {
                    foreach ($records as $record) {
                        if (!empty($record['ip'])) {
                            $ips[] = $record['ip'];
                        } elseif (!empty($record['ipv6'])) {
                            $ips[] = $record['ipv6'];
                        }
                    }
                }
            }
            if (!$ips) {
                $resolved = @gethostbynamel($host);
                if (is_array($resolved)) {
                    $ips = $resolved;
                }
            }
            if (!$ips) {
                $single = @gethostbyname($host);
                if ($single && $single !== $host) {
                    $ips[] = $single;
                }
            }
        }

        return array_values(array_unique($ips));
    }

    /**
     * Определяет, является ли IP-адрес публичным (не внутренним/служебным).
     *
     * @param string $ip
     * @return bool
     */
    protected function isPublicAddress($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = inet_pton($ip);
            if ($packed !== false) {
                // IPv4-mapped (::ffff:127.0.0.1) и IPv4-compatible (::127.0.0.1)
                // адреса разбираем на встроенный IPv4 и проверяем его отдельно.
                $mapped = strncmp($packed, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff", 12) === 0;
                $compat = strncmp($packed, str_repeat("\x00", 12), 12) === 0;
                if ($mapped || $compat) {
                    return $this->isPublicAddress(inet_ntop(substr($packed, 12)));
                }
            }

            // Диапазоны, инкапсулирующие IPv4 (NAT64/6to4), позволяют обойти
            // IPv4-проверки при наличии соответствующего шлюза.
            if (preg_match('/^(64:ff9b:|2002:)/i', $ip)) {
                return false;
            }

            // Приватные (fc00::/7), служебные (::1, fe80::/10, ::/128) и прочие
            // диапазоны отсекаются фильтрами PHP.
            return filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        // Фильтры PHP отсекают приватные (10/8, 172.16/12, 192.168/16)
        // и служебные (127/8, 169.254/16, 0/8) диапазоны.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        // Диапазоны, которые не отсекаются фильтрами PHP: CGNAT и служебные.
        $long = ip2long($ip);
        foreach (['100.64.0.0/10', '192.0.0.0/24', '198.18.0.0/15'] as $cidr) {
            list($network, $bits) = explode('/', $cidr);
            $mask = -1 << (32 - (int)$bits);
            if (($long & $mask) === (ip2long($network) & $mask)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Возвращает путь к локальному временному файлу для скачиваемого ресурса.
     *
     * @param string $url
     * @return string
     */
    protected function getRemoteTempPath($url)
    {
        // Скачанное содержимое размещаем во временной директории системы, а не
        // в web-доступной папке загрузок: до валидации (checkFileAndReturnError)
        // и сохранения через Bitrix API файл не должен быть доступен по HTTP.
        $dir = rtrim((string)sys_get_temp_dir(), '/\\');
        if ($dir === '' || !@is_dir($dir) || !@is_writable($dir)) {
            $dir = rtrim($this->get_upload_path(), '/\\');
            if (!is_dir($dir)) {
                mkdir($dir, $this->options['mkdir_mode'], true);
            }
        }

        $extension = strtolower((string)pathinfo((string)parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (!preg_match('/^[a-z0-9]{1,10}$/', $extension)) {
            $extension = '';
        }

        return $dir . '/remote_' . md5($url . uniqid('', true)) . ($extension ? '.' . $extension : '');
    }

    /**
     * Резервное скачивание потоком, если расширение curl недоступно.
     * Соединение выполняется по проверенному IP, TLS-сертификат и SNI
     * проверяются относительно исходного хоста, редиректы запрещены.
     *
     * @return bool
     */
    protected function downloadWithStream($url, $host, $port, $ip, $target): bool
    {
        $parts = parse_url($url);
        $path = !empty($parts['path']) ? $parts['path'] : '/';
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        $hostHeader = $port === 443 ? $host : $host . ':' . $port;
        $ipLiteral = strpos($ip, ':') !== false ? '[' . $ip . ']' : $ip;

        $context = stream_context_create(
            [
                'http' => [
                    'method'          => 'GET',
                    // Редиректы запрещены: адрес цели редиректа не проверен.
                    'follow_location' => 0,
                    'max_redirects'   => 0,
                    'ignore_errors'   => true,
                    'timeout'         => (int)$this->options['resource_timeout'],
                    'header'          => "Host: {$hostHeader}\r\nUser-Agent: sprint.editor\r\n",
                ],
                'ssl'  => [
                    // Соединяемся с проверенным IP, но TLS-сертификат и SNI
                    // проверяем относительно исходного хоста.
                    'verify_peer'      => true,
                    'verify_peer_name' => true,
                    'peer_name'        => $host,
                    'SNI_enabled'      => true,
                    'SNI_server_name'  => $host,
                ],
            ]
        );

        $remote = @fopen(
            'https://' . $ipLiteral . ($port === 443 ? '' : ':' . $port) . $path,
            'rb',
            false,
            $context
        );
        if (!$remote) {
            return false;
        }

        $status = 0;
        if (isset($http_response_header[0])
            && preg_match('#^HTTP/\S+\s+(\d{3})#', $http_response_header[0], $matches)
        ) {
            $status = (int)$matches[1];
        }
        if ($status < 200 || $status >= 300) {
            fclose($remote);
            return false;
        }

        $out = @fopen($target, 'wb');
        if (!$out) {
            fclose($remote);
            return false;
        }

        $maxSize = (int)$this->options['resource_max_file_size'];
        $written = 0;
        while (!feof($remote)) {
            $chunk = fread($remote, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $written += strlen($chunk);
            if ($maxSize > 0 && $written > $maxSize) {
                fclose($out);
                fclose($remote);
                return false;
            }
            fwrite($out, $chunk);
        }
        fclose($out);
        fclose($remote);

        return true;
    }

    public function updateDesc(int $id, string $desc)
    {
        if ($id > 0) {
            $item = CFile::GetByID($id)->Fetch();
            if ($item && $item['MODULE_ID'] == Module::ID) {
                CFile::UpdateDesc($id, $desc);
            }
        }
    }

    private function checkSvgFile(string $content): bool
    {

        // 3. Проверка на <script> теги (с учетом пробелов и атрибутов)
        if (preg_match('/<\s*script/i', $content)) {
            return false;
        }

        // 4. Проверка on* событий (с учетом пробелов)
        if (preg_match('/on\w+\s*=\s*["\']/i', $content)) {
            return false;
        }

        // 5. Проверка javascript: в ссылках
        if (preg_match('/javascript\s*:/i', $content)) {
            return false;
        }

        // 6. Проверка CDATA с потенциальным кодом
        if (preg_match('/<!\[CDATA\[.*?function.*?\]\]>/is', $content) ||
            preg_match('/<!\[CDATA\[.*?eval.*?\]\]>/is', $content)) {
            return false;
        }

        // 7. Проверка data: URI (может содержать XSS)
        if (preg_match('/data\s*:\s*text\/html/i', $content) ||
            preg_match('/data\s*:\s*application\/x-javascript/i', $content)) {
            return false;
        }

        // 8. Проверка опасных тегов
        $dangerousTags = ['iframe', 'object', 'embed', 'form', 'input', 'button'];
        foreach ($dangerousTags as $tag) {
            if (preg_match('/<\s*' . $tag . '/i', $content)) {
                return false;
            }
        }

        // 9. Проверка внешних ресурсов
        if (preg_match('/xlink:href\s*=\s*["\']https?:\/\//i', $content)) {
            return false;
        }

        // 10. Проверка обфускации через числовые коды
        if (preg_match('/&#\d+;/', $content)) {
            // Декодируем и проверяем
            $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/script|javascript|on\w+=/i', $decoded)) {
                return false;
            }
        }

        // 11. Проверка стилей с expression (для IE)
        if (preg_match('/expression\s*\(/i', $content)) {
            return false;
        }

        // 12. Проверка на пустоту (маленькие вредоносные файлы)
        if (strlen($content) < 50) {
            return false;
        }

        return true;
    }

}
