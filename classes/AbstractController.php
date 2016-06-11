<?php

/*
Copyright 2013-2016 Christoph M. Becker

This file is part of Extedit_XH.

Extedit_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Extedit_XH is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Extedit_XH.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Extedit;

abstract class AbstractController
{
    /**
     * @var bool
     */
    private static $isEditorInitialized = false;

    /**
     * @param string $username
     * @return bool
     */
    protected function isAuthorizedToEdit($username)
    {
        return $this->isAdmin()
            || $username == '*' && $this->getCurrentUser()
            || in_array($this->getCurrentUser(), explode(',', $username));
    }

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
        if (!$this->isAdmin() && in_array($editor, array('tinymce'))) {
            include_once "{$plugins}extedit/connectors/$editor.php";
            $hjs .= extedit_tinymce_init() . "\n";
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

    /**
     * @param string $_template
     * @param array  $_bag
     * @return string
     */
    protected function render($_template, $_bag)
    {
        global $pth, $cf;

        $_template = "{$pth['folder']['plugins']}extedit/views/$_template.php";
        $_xhtml = strtolower($cf['xhtml']['endtags']) == 'true';
        unset($pth, $cf);
        extract($_bag);
        ob_start();
        include $_template;
        $view = ob_get_clean();
        if (!$_xhtml) {
            $view = str_replace(' />', '>', $view);
        }
        return $view;
    }

    /**
     * @return void
     */
    protected function preventAccess()
    {
        // do nothing!
    }
}
