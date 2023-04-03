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

use Extedit\Infra\Request;
use Extedit\Infra\Responder;
use Extedit\Value\Response;
use XH\CSRFProtection as CsrfProtector;

class FunctionController
{
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

    /** @var ContentRepo */
    private $contentRepo;

    /** @var Session */
    private $session;

    /** @var Editor */
    private $editor;

    /** @var View */
    private $view;

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
        ContentRepo $contentRepo,
        Session $session,
        Editor $editor,
        View $view,
        $username,
        $textname = null
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->baseFolder = $baseFolder;
        $this->imageFolder = $imageFolder;
        $this->configuredEditor = $configuredEditor;
        $this->conf = $conf;
        $this->lang = $lang;
        $this->contentRepo = $contentRepo;
        $this->session = $session;
        $this->editor = $editor;
        $this->view = $view;
        $this->username = $username;
        $this->textname = $textname;
        $this->sanitizeTextname();
    }

    /**
     * @return string (X)HTML
     */
    public function handle(Request $request)
    {
        global $sn, $su;

        $o = '';
        if ($this->isAuthorizedToEdit($request, $this->username)) {
            if (isset($_GET['extedit_imagepicker'])) {
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
                if ($_GET['extedit_imagepicker'] !== "upload") {
                    ob_end_clean(); // necessary if called from template
                    echo Responder::respond($imagePicker->show());
                    exit;
                }
                if ($_GET['extedit_imagepicker'] === "upload") {
                    echo Responder::respond($imagePicker->handleUpload(new Upload($_FILES['extedit_file'])));
                    exit;
                }
            }
            if (isset($_POST["extedit_{$this->textname}_text"])) {
                $o .= Responder::respond($this->handleSave());
            } else {
                $this->content = $this->contentRepo->findByName($this->textname);
            }
            if ($this->isEditModeRequested()) {
                $o .= $this->renderEditForm();
                $this->editor->init();
            } else {
                $o .= $this->getEditLink() . $this->evaluatePlugincall();
            }
        } else {
            $this->content = $this->contentRepo->findByName($this->textname);
            $o .= $this->evaluatePlugincall();
        }
        return $o;
    }

    /**
     * @param string $username
     * @return bool
     */
    private function isAuthorizedToEdit(Request $request, $username)
    {
        return $request->admin()
            || $username == '*' && $this->session->get('username', '')
            || in_array($this->session->get('username', ''), explode(',', $username));
    }

    /**
     * @return string
     */
    private function getImageFolder()
    {
        $subfolder = $this->conf['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $this->session->get('username', ''))
            : '';
        return rtrim($this->imageFolder . $subfolder, '/') . '/';
    }

    private function handleSave(): Response
    {
        global $su;

        $this->content = $_POST["extedit_{$this->textname}_text"];
        $mtime = $this->contentRepo->findLastModification($this->textname);
        if ($_POST["extedit_{$this->textname}_mtime"] >= $mtime) {
            if ($this->contentRepo->save($this->textname, $this->content)) {
                return Response::redirect(CMSIMPLE_URL . "?$su&extedit_mode=edit");
            } else {
                return Response::create($this->view->error("err_save", $this->contentRepo->filename($this->textname)));
            }
        } else {
            return Response::create($this->view->error("err_changed", $this->textname));
        }
    }

    /**
     * @return string (X)HTML
     */
    private function renderEditForm(): string
    {
        global $sn, $su;

        return $this->view->render('edit_form', [
            'editUrl' => "$sn?$su",
            'textareaName' => "extedit_{$this->textname}_text",
            'content' => $this->content,
            'mtimeName' => "extedit_{$this->textname}_mtime",
            'mtime' => $this->contentRepo->findLastModification($this->textname),
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
    private function evaluatePlugincall()
    {
        if ($this->conf['allow_scripting']) {
            return evaluate_plugincall($this->content);
        }
        return $this->content;
    }
}
