<?php

/**
 * Copyright 2013-2017 Christoph M. Becker
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

abstract class AbstractController
{
    /**
     * @var bool
     */
    private static $isEditorInitialized = false;

    /**
     * @return void
     * @todo Image picker for other editors
     */
    protected function initEditor()
    {
        global $pth, $hjs, $cf;

        if (self::$isEditorInitialized) {
            return;
        }
        self::$isEditorInitialized = true;
        $plugins = $pth['folder']['plugins'];
        $editor = $cf['editor']['external'];
        if (!$this->isAdmin() && in_array($editor, array('ckeditor', 'tinymce', 'tinymce4'))) {
            include_once "{$plugins}extedit/connectors/$editor.php";
            $func = "extedit_{$editor}_init";
            $hjs .= $func() . "\n";
            $config = file_get_contents("{$plugins}extedit/inits/$editor.js");
        } else {
            $config = false;
        }
        init_editor(array('xh-editor'), $config);
    }

    /**
     * @return bool
     */
    protected function isAdmin()
    {
        return defined('XH_ADM') && XH_ADM;
    }

    /**
     * @return string
     */
    protected function getCurrentUser()
    {
        if (session_id() == '') {
            session_start();
        }
        return isset($_SESSION['username'])
            ? $_SESSION['username']
            : '';
    }
}
