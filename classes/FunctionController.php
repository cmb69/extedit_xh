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

use XH\CSRFProtection as CsrfProtector;

class FunctionController
{
    /**
     * @var bool
     */
    private static $isEditorInitialized = false;

    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $baseFolder;

    /** @var string */
    private $imageFolder;

    /** @var string */
    private $configuredEditor;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

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
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     * @param string $username
     * @param string|null $textname
     */
    public function __construct(
        string $pluginFolder,
        string $baseFolder,
        string $imageFolder,
        string $configuredEditor,
        array $conf,
        array $lang,
        $username,
        $textname = null
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->baseFolder = $baseFolder;
        $this->imageFolder = $imageFolder;
        $this->configuredEditor = $configuredEditor;
        $this->conf = $conf;
        $this->lang = $lang;
        $this->username = $username;
        $this->textname = $textname;
        $this->sanitizeTextname();
    }

    /**
     * @return string (X)HTML
     */
    public function handle()
    {
        global $pth, $sn, $su;

        $o = '';
        if ($this->isAuthorizedToEdit($this->username)) {
            if (isset($_GET['extedit_imagepicker'])) {
                ob_end_clean(); // necessary if called from template
                $imagePicker = new ImagePicker(
                    $this->pluginFolder,
                    $this->baseFolder,
                    $this->getImageFolder(),
                    $sn,
                    $su,
                    $this->conf,
                    $this->lang,
                    $this->configuredEditor,
                    new ImageFinder($this->lang['imagepicker_dimensions']),
                    new CsrfProtector('extedit_csrf_token')
                );
                echo $imagePicker->show()->trigger();
                exit;
            }
            if (isset($_GET['extedit_upload'])) {
                $imagePicker = new ImagePicker(
                    $this->pluginFolder,
                    $this->baseFolder,
                    $this->getImageFolder(),
                    $sn,
                    $su,
                    $this->conf,
                    $this->lang,
                    $this->configuredEditor,
                    new ImageFinder($this->lang['imagepicker_dimensions']),
                    new CsrfProtector('extedit_csrf_token')
                );
                $imagePicker->handleUpload();
                exit;
            }
            if (isset($_POST["extedit_{$this->textname}_text"])) {
                $o .= $this->handleSave();
            } else {
                $this->content = $this->read();
            }
            if ($this->isEditModeRequested()) {
                $o .= $this->renderEditForm();
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
     * @return string
     */
    private function getImageFolder()
    {
        $subfolder = $this->conf['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $this->getCurrentUser())
            : '';
        return rtrim($this->imageFolder . $subfolder, '/') . '/';
    }

    /**
     * @return string (X)HTML
     */
    private function handleSave()
    {
        global $su;

        $this->content = $_POST["extedit_{$this->textname}_text"];
        $mtime = $this->mtime();
        if ($_POST["extedit_{$this->textname}_mtime"] >= $mtime) {
            if ($this->write()) {
                header('Location: ' . CMSIMPLE_URL . "?$su&extedit_mode=edit");
                exit;
            } else {
                return XH_message(
                    'fail',
                    $this->lang['err_save'],
                    Content::getFilename($this->textname)
                );
            }
        } else {
            return XH_message('fail', $this->lang['err_changed'], $this->textname);
        }
    }

    /**
     * @return string (X)HTML
     */
    private function renderEditForm(): string
    {
        global $sn, $su;

        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return $view->render('edit_form', [
            'editUrl' => "$sn?$su",
            'textareaName' => "extedit_{$this->textname}_text",
            'content' => $this->content,
            'mtimeName' => "extedit_{$this->textname}_mtime",
            'mtime' => $this->mtime(),
        ]);
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
        global $hjs;

        if (self::$isEditorInitialized) {
            return;
        }
        self::$isEditorInitialized = true;
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
        init_editor(array('xh-editor'), $config);
    }

    /**
     * @return string (X)HTML
     */
    private function getEditLink()
    {
        global $sn, $su;

        return "<a href=\"$sn?$su&amp;extedit_mode=edit\">" . $this->lang['mode_edit'] . '</a>';
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
            return (int) filemtime($filename);
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
        if ($this->conf['allow_scripting']) {
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
