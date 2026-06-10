<?php

namespace Sprint\Editor\Blocks;

use Sprint\Editor\Module;

class Snippet
{
    static public function includeSnippet($block): void
    {

        Module::includeModuleFile(
            Module::getSnippetsDir(),
            basename((string)$block['file'])
        );
    }
}
