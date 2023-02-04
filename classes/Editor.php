<?php

/**
 * Copyright 2013-2023 Christoph M. Becker
 *
 * This file is part of Extedit_XH.
 *
 * Extedit_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Extedit_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Extedit_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Extedit;

class Editor
{
    /** @var bool */
    private $isEditorInitialized = false;

    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $configuredEditor;

    public function __construct(string $pluginFolder, string $configuredEditor)
    {
        $this->pluginFolder = $pluginFolder;
        $this->configuredEditor = $configuredEditor;
    }

    /**
     * @return void
     * @todo Image picker for other editors
     */
    public function init()
    {
        global $hjs;

        if ($this->isEditorInitialized) {
            return;
        }
        $this->isEditorInitialized = true;
        $editor = $this->configuredEditor;
        if (!(defined('XH_ADM') && XH_ADM) && in_array($editor, array('ckeditor', 'tinymce', 'tinymce4'))) {
            include_once "{$this->pluginFolder}connectors/$editor.php";
            $func = "extedit_{$editor}_init";
            assert(is_callable($func));
            $hjs .= $func() . "\n";
            $config = file_get_contents("{$this->pluginFolder}inits/$editor.js");
        } else {
            $config = false;
        }
        $this->doInit($config);
    }

    /**
     * @param string|false $config
     * @return void
     */
    private function doInit($config)
    {
        init_editor(array('xh-editor'), $config);
    }
}
