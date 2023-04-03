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

class FunctionController
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var ContentRepo */
    private $contentRepo;

    /** @var Editor */
    private $editor;

    /** @var View */
    private $view;

    /**
     * @var string
     */
    private $content;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     */
    public function __construct(
        array $conf,
        array $lang,
        ContentRepo $contentRepo,
        Editor $editor,
        View $view
    ) {
        $this->conf = $conf;
        $this->lang = $lang;
        $this->contentRepo = $contentRepo;
        $this->editor = $editor;
        $this->view = $view;
    }

    /**
     * @return string (X)HTML
     */
    public function handle(Request $request, string $username, ?string $textname)
    {
        $textname = $this->sanitizeTextname($textname);
        $o = '';
        if ($this->isAuthorizedToEdit($request, $username)) {
            if (isset($_POST["extedit_{$textname}_text"])) {
                $o .= Responder::respond($this->handleSave($textname));
            } else {
                $this->content = $this->contentRepo->findByName($textname);
            }
            if ($this->isEditModeRequested()) {
                $o .= $this->renderEditForm($textname);
                $this->editor->init();
            } else {
                $o .= $this->getEditLink() . $this->evaluatePlugincall();
            }
        } else {
            $this->content = $this->contentRepo->findByName($textname);
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
            || $username == '*' && $request->user()
            || in_array($request->user(), explode(',', $username));
    }

    private function handleSave(string $textname): Response
    {
        global $su;

        $this->content = $_POST["extedit_{$textname}_text"];
        $mtime = $this->contentRepo->findLastModification($textname);
        if ($_POST["extedit_{$textname}_mtime"] >= $mtime) {
            if ($this->contentRepo->save($textname, $this->content)) {
                return Response::redirect(CMSIMPLE_URL . "?$su&extedit_mode=edit");
            } else {
                return Response::create($this->view->error("err_save", $this->contentRepo->filename($textname)));
            }
        } else {
            return Response::create($this->view->error("err_changed", $textname));
        }
    }

    /**
     * @return string (X)HTML
     */
    private function renderEditForm(string $textname): string
    {
        global $sn, $su;

        return $this->view->render('edit_form', [
            'editUrl' => "$sn?$su",
            'textareaName' => "extedit_{$textname}_text",
            'content' => $this->content,
            'mtimeName' => "extedit_{$textname}_mtime",
            'mtime' => $this->contentRepo->findLastModification($textname),
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

    private function sanitizeTextname(?string $textname): string
    {
        global $h, $s;

        // TODO: check that $s is valid?
        if (!isset($textname)) {
            $textname = $h[max($s, 0)];
        }
        return preg_replace('/[^a-z0-9-]/i', '', $textname);
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
