<?php

namespace Sprint\Editor\Cleaner;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class EditorTools
{
    public function getFileIds($editorJson): array
    {
        $fileIds = [];
        $haystack = json_decode($editorJson, true);
        if (!is_array($haystack)) {
            return $fileIds;
        }

        $iterator = new RecursiveArrayIterator($haystack);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($recursive as $key => $value) {
            if ($key === 'file' && !empty($value['ID']) && !empty($value['SRC'])) {
                $fileIds[] = $value['ID'];
            }
        }
        return $fileIds;
    }
}
