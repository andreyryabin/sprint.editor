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
use CUtil;
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

    function __construct($options = null, $initialize = true, $error_messages = null)
    {
        $dir = '/upload/sprint.editor/temp/';
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $dir)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . $dir, BX_DIR_PERMISSIONS, true);
        }

        $this->response = [];
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
            'print_response'                   => true,
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
                $this->post($this->options['print_response']);
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

    function get_config_bytes($val)
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
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fix_integer_overflow((int)$content_range[1]);
        while (is_file($this->get_upload_path($name))) {
            if ($uploaded_bytes === $this->get_file_size(
                    $this->get_upload_path($name)
                )
            ) {
                break;
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
    ) {
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

    protected function body($str)
    {
        echo $str;
    }

    protected function header($str)
    {
        header($str);
    }

    protected function get_upload_data($id)
    {
        return @$_FILES[$id];
    }

    protected function get_post_param($id)
    {
        return @$_POST[$id];
    }

    protected function get_query_param($id)
    {
        return @$_GET[$id];
    }

    protected function get_server_var($id)
    {
        return @$_SERVER[$id];
    }

    protected function handle_form_data($file, $index)
    {
        // Handle form data, e.g. $_POST['description'][$index]
    }

    protected function get_version_param()
    {
        return basename(stripslashes($this->get_query_param('version')));
    }

    protected function get_singular_param_name()
    {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function get_file_name_param()
    {
        $name = $this->get_singular_param_name();
        return basename(stripslashes($this->get_query_param($name)));
    }

    protected function get_file_names_params()
    {
        $params = $this->get_query_param($this->options['param_name']);
        if (!$params) {
            return null;
        }
        foreach ($params as $key => $value) {
            $params[$key] = basename(stripslashes($value));
        }
        return $params;
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

    public function generate_response($content, $print_response = true)
    {
        $this->response = $content;
        if ($print_response) {
            $json = json_encode($content);
            $redirect = stripslashes($this->get_post_param('redirect'));
            if ($redirect && preg_match($this->options['redirect_allow_target'], $redirect)) {
                $this->header('Location: ' . sprintf($redirect, rawurlencode($json)));
                return '';
            }
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
            $this->body($json);
        }
        return $content;
    }

    public function get_response()
    {
        return $this->response;
    }

    public function head()
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

    public function post($print_response = true)
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
                        $file_name ? $file_name : $upload['name'][$index],
                        $size ? $size : $upload['size'][$index],
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
                    $file_name
                        ? $file_name
                        : ($upload['name'] ?? null),
                    $size
                        ? $size
                        : ($upload['size'] ?? $this->get_server_var('CONTENT_LENGTH')),
                    $upload['type'] ?? $this->get_server_var('CONTENT_TYPE'),
                    $upload['error'] ?? null,
                    null,
                    $content_range
                );
            }
        }

        $files = $this->bitrixSaveCollection($files);

        return $this->generate_response(
            [$this->options['param_name'] => $files],
            $print_response
        );
    }

    //bitrix section

    protected function bitrixTranslite($str)
    {
        return CUtil::translit(
            $str, 'ru', [
                "max_len"               => 100,
                "change_case"           => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
                "replace_space"         => '-',
                "replace_other"         => '-',
                "delete_repeat_replace" => true,
                "safe_chars"            => '.()',
            ]
        );
    }

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

        foreach ($files as $k => $file) {
            $aFile = CFile::MakeFileArray($file->path);
            $aFile['MODULE_ID'] = 'sprint.editor';
            if (!empty($this->options['bitrix_resize'])) {
                if ($aFile['type'] == 'image/svg+xml' || $aFile['type'] == 'image/svg') {
                    $bitrixId = CFile::SaveFile($aFile, 'sprint.editor');
                    if ($bitrixId) {
                        $res[] = Image::resizeImage2($bitrixId, $this->options['bitrix_resize']);
                    }
                } else {
                    $checkErr = CFile::CheckImageFile($aFile);
                    if (empty($checkErr)) {
                        $bitrixId = CFile::SaveFile($aFile, 'sprint.editor');
                        if ($bitrixId) {
                            $res[] = Image::resizeImage2($bitrixId, $this->options['bitrix_resize']);
                        }
                    }
                }
            } else {
                $checkErr = CFile::CheckFile($aFile);
                if (empty($checkErr)) {
                    $bitrixId = CFile::SaveFile($aFile, 'sprint.editor');
                    if ($bitrixId) {
                        $res[] = CFile::GetFileArray($bitrixId);
                    }
                }
            }

            unlink($file->path);
        }
        return $res;
    }

    public function saveResource($url)
    {
        if (!empty($this->options['bitrix_resize'])) {
            $imgurl = Youtube::getPreviewImg($url);
            if ($imgurl) {
                return $this->generate_response(
                    [
                        'image' => $this->bitrixSaveOne($imgurl),
                        'video' => $url,
                    ], true
                );
            } else {
                return $this->generate_response(
                    [
                        'image' => $this->bitrixSaveOne($url),
                    ], true
                );
            }
        } else {
            return $this->generate_response(
                [
                    'file' => $this->bitrixSaveOne($url),
                ], true
            );
        }
    }
}
