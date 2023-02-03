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

class FunctionController
{
    /**
     * @var bool
     */
    private static $isEditorInitialized = false;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string|null
     */
    private $textname;

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $username
     * @param string|null $textname
     */
    public function __construct($username, $textname = null)
    {
        $this->username = $username;
        $this->textname = $textname;
        $this->sanitizeTextname();
    }

    /**
     * @return string (X)HTML
     */
    public function handle()
    {
        $o = '';
        if ($this->isAuthorizedToEdit($this->username)) {
            if (isset($_GET['extedit_imagepicker'])) {
                ob_end_clean(); // necessary if called from template
                $imagePicker = new ImagePicker();
                echo $imagePicker->show();
                exit;
            }
            if (isset($_GET['extedit_upload'])) {
                $imagePicker = new ImagePicker();
                $imagePicker->handleUpload();
                exit;
            }
            if (isset($_POST["extedit_{$this->textname}_text"])) {
                $o .= $this->handleSave();
            } else {
                $this->content = $this->read();
            }
            if ($this->isEditModeRequested()) {
                $o .= $this->getViewLink() . $this->getEditForm();
                $this->initEditor();
            } else {
                $o .= $this->getEditLink() . $this->evaluatePlugincall();
            }
        } else {
            $this->content = $this->read();
            $o .= $this->evaluatePlugincall();
        }
        return $o;
    }

    /**
     * @param string $username
     * @return bool
     */
    private function isAuthorizedToEdit($username)
    {
        return (defined('XH_ADM') && XH_ADM)
            || $username == '*' && $this->getCurrentUser()
            || in_array($this->getCurrentUser(), explode(',', $username));
    }

    /**
     * @return string (X)HTML
     */
    private function handleSave()
    {
        global $su, $plugin_tx;

        $this->content = $_POST["extedit_{$this->textname}_text"];
        $mtime = $this->mtime();
        if ($_POST["extedit_{$this->textname}_mtime"] >= $mtime) {
            if ($this->write()) {
                header('Location: ' . CMSIMPLE_URL . "?$su&extedit_mode=edit");
                exit;
            } else {
                return XH_message(
                    'fail',
                    $plugin_tx['extedit']['err_save'],
                    Content::getFilename($this->textname)
                );
            }
        } else {
            return XH_message('fail', $plugin_tx['extedit']['err_changed'], $this->textname);
        }
    }

    /**
     * @return string (X)HTML
     */
    private function getEditForm()
    {
        return '<form action="" method="POST">'
            . '<textarea name="extedit_' . $this->textname . '_text" cols="80"'
            . ' rows="25" class="xh-editor" style="width: 100%">'
            . XH_hsc($this->content)
            . '</textarea>'
            . tag(
                'input type="hidden" name="extedit_' . $this->textname . '_mtime"'
                . ' value="' . $this->mtime() . '"'
            )
            . '</form>';
    }

    /**
     * @return bool
     */
    private function isEditModeRequested()
    {
        return isset($_GET['extedit_mode']) && $_GET['extedit_mode'] === 'edit';
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
        if (!(defined('XH_ADM') && XH_ADM) && in_array($editor, array('ckeditor', 'tinymce', 'tinymce4'))) {
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
     * @return string (X)HTML
     */
    private function getEditLink()
    {
        global $s, $plugin_tx;

        return a($s, '&amp;extedit_mode=edit') . $plugin_tx['extedit']['mode_edit'] . '</a>';
    }

    /**
     * @return string (X)HTML
     */
    private function getViewLink()
    {
        global $s, $plugin_tx;

        return a($s, '') . $plugin_tx['extedit']['mode_view'] . '</a>';
    }

    /**
     * @return void
     */
    private function sanitizeTextname()
    {
        global $h, $s;

        // TODO: check that $s is valid?
        if (!isset($this->textname)) {
            $this->textname = $h[max($s, 0)];
        }
        $this->textname = preg_replace('/[^a-z0-9-]/i', '', $this->textname);
    }

    /**
     * @return string
     */
    private function read()
    {
        $content = Content::find($this->textname);
        return $content->getHtml();
    }

    /**
     * @return int
     */
    private function mtime()
    {
        $filename = Content::getFilename($this->textname);
        if (file_exists($filename)) {
            return filemtime($filename);
        } else {
            return 0;
        }
    }

    /**
     * @return bool
     */
    private function write()
    {
        $filename = Content::getFilename($this->textname);
        return (!file_exists($filename) || is_writable($filename))
            && file_put_contents($filename, $this->content) !== false;
    }

    /**
     * @return string
     */
    private function evaluatePlugincall()
    {
        global $plugin_cf;

        if ($plugin_cf['extedit']['allow_scripting']) {
            return evaluate_plugincall($this->content);
        }
        return $this->content;
    }

    /**
     * @return string
     */
    private function getCurrentUser()
    {
        XH_startSession();
        return isset($_SESSION['username'])
            ? $_SESSION['username']
            : '';
    }
}
