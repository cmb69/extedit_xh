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

class FunctionController extends AbstractController
{
    /**
     * @var bool
     */
    private static $isEditorInitialized = false;

    /**
     * @param string $username
     * @param string $textname
     * @return string (X)HTML
     */
    public function handle($username, $textname = '')
    {
        global $s, $e, $plugin_tx;

        $ptx = $plugin_tx['extedit'];
        $textname = $this->textname($textname);
        if (!isset($_POST["extedit_{$textname}_text"])) {
            $content = $this->read($textname);
        }
        if ($this->isAuthorizedToEdit($username)) {
            $mtime = $this->mtime($textname);
            if (isset($_POST["extedit_{$textname}_text"])) {
                $content = stsl($_POST["extedit_{$textname}_text"]);
                if ($_POST["extedit_{$textname}_mtime"] >= $mtime) {
                    $this->write($textname, $content);
                    $mtime = time(); // to avoid calling clearstatcache()
                } else {
                    $e .= '<li>' . sprintf($ptx['err_changed'], $textname)
                        . '</li>';
                }
            }
            if (isset($_GET['extedit_mode']) && $_GET['extedit_mode'] === 'edit') {
                $o = a($s, '') . $ptx['mode_view'] . '</a>'
                    . '<form action="" method="POST">'
                    . '<textarea name="extedit_' . $textname . '_text" cols="80"'
                    . ' rows="25" class="xh-editor" style="width: 100%">'
                    . XH_hsc($content)
                    . '</textarea>'
                    . tag(
                        'input type="hidden" name="extedit_' . $textname . '_mtime"'
                        . ' value="' . $mtime . '"'
                    )
                    . '</form>';
                $this->initEditor();
            } else {
                $o = a($s, '&amp;extedit_mode=edit') . $ptx['mode_edit'] . '</a>'
                    . $this->evaluatePlugincall($content);
            }
        } else {
            $o = $this->evaluatePlugincall($content);
        }
        return $o;
    }

    /**
     * @param string $username
     * @return bool
     */
    private function isAuthorizedToEdit($username)
    {
        return $this->isAdmin()
            || $username == '*' && $this->getCurrentUser()
            || $this->getCurrentUser() == $username;
    }

    /**
     * @param string $textname
     * @return string
     */
    private function textname($textname)
    {
        global $h, $s;

        // TODO: check that $s is valid?
        if (empty($textname)) {
            $textname = $h[max($s, 0)];
        }
        $textname = preg_replace('/[^a-z0-9-]/i', '', $textname);
        return $textname;
    }

    /**
     * @param string $textname
     * @return string
     */
    private function read($textname)
    {
        $content = Content::find($textname);
        if ($content->getHtml() !== null) {
            return $content->getHtml();
        } else {
            e('cntopen', 'content', Content::getFilename($textname));
        }
    }

    /**
     * @param string $textname
     * @return int
     */
    private function mtime($textname)
    {
        $filename = Content::getFilename($textname);
        if (file_exists($filename)) {
            return filemtime($filename);
        } else {
            return 0;
        }
    }

    /**
     * @param string $textname
     * @param string $contents
     * @return void
     */
    private function write($textname, $contents)
    {
        $filename = Content::getFilename($textname);
        if (file_put_contents($filename, $contents) === false) {
            e('cntsave', 'content', $filename);
        }
    }

    /**
     * @return void
     * @todo Image picker for other editors
     */
    private function initEditor()
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
     * @param string $content
     * @return string
     */
    private function evaluatePlugincall($content)
    {
        global $plugin_cf;

        if ($plugin_cf['extedit']['allow_scripting']) {
            $content = evaluate_plugincall($content);
        }
        return $content;
    }
}
