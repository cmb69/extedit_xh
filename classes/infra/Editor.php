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

namespace Extedit\Infra;

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
        $connector = "{$this->pluginFolder}connectors/$editor.php";
        $init = "{$this->pluginFolder}inits/$editor.js";
        if (!(defined('XH_ADM') && XH_ADM) && is_readable($connector) && is_readable($init)) {
            include_once $connector;
            $func = "extedit_{$editor}_init";
            if (is_callable($func)) {
                $hjs .= $func() . "\n";
                $config = file_get_contents($init);
            } else {
                $config = false;
            }
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
