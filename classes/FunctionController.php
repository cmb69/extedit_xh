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
use Extedit\Value\Response;

class FunctionController
{
    /** @var array<string,string> */
    private $conf;

    /** @var ContentRepo */
    private $contentRepo;

    /** @var Editor */
    private $editor;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        ContentRepo $contentRepo,
        Editor $editor,
        View $view
    ) {
        $this->conf = $conf;
        $this->contentRepo = $contentRepo;
        $this->editor = $editor;
        $this->view = $view;
    }

    public function handle(Request $request, string $username, ?string $textname): Response
    {
        $textname = $this->sanitizeTextname($textname);
        switch ($request->action($textname)) {
            default:
                return $this->view($request, $username, $textname);
            case "edit":
                return $this->edit($request, $username, $textname);
            case "do_edit":
                return $this->handleSave($request, $username, $textname);
        }
    }

    private function view(Request $request, string $username, string $textname): Response
    {
        $content = $this->contentRepo->findByName($textname);
        return Response::create($this->renderView($this->isAuthorizedToEdit($request, $username), $content));
    }

    private function edit(Request $request, string $username, string $textname): Response
    {
        if (!$this->isAuthorizedToEdit($request, $username)) {
            return Response::create($this->view->error("err_unauthorized"));
        }
        $this->editor->init();
        return Response::create($this->renderEditForm($textname, $this->contentRepo->findByName($textname)));
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

    private function handleSave(Request $request, string $username, string $textname): Response
    {
        global $su;

        if (!$this->isAuthorizedToEdit($request, $username)) {
            return Response::create($this->view->error("err_unauthorized"));
        }
        $content = $_POST["extedit_{$textname}_text"];
        $mtime = $this->contentRepo->findLastModification($textname);
        if ($_POST["extedit_{$textname}_mtime"] < $mtime) {
            $this->editor->init();
            return Response::create($this->view->error("err_changed", $textname)
                . $this->renderEditForm($textname, $content));
        }
        if (!$this->contentRepo->save($textname, $content)) {
            $this->editor->init();
            return Response::create($this->view->error("err_save", $this->contentRepo->filename($textname))
                . $this->renderEditForm($textname, $content));
        }
        return Response::redirect(CMSIMPLE_URL . "?$su&extedit_action=edit");
    }

    /**
     * @return string (X)HTML
     */
    private function renderEditForm(string $textname, string $content): string
    {
        global $sn, $su;

        return $this->view->render('edit_form', [
            'editUrl' => "$sn?$su",
            'textareaName' => "extedit_{$textname}_text",
            'content' => $content,
            'mtimeName' => "extedit_{$textname}_mtime",
            'mtime' => $this->contentRepo->findLastModification($textname),
        ]);
    }

    private function renderView(bool $mayEdit, string $content): string
    {
        global $sn, $su;
        return $this->view->render("view", [
            "may_edit" => $mayEdit,
            "url" => "$sn?$su&extedit_action=edit",
            "content" => $this->evaluatePlugincall($content),
        ]);
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
    private function evaluatePlugincall(string $content)
    {
        if ($this->conf['allow_scripting']) {
            return evaluate_plugincall($content);
        }
        return $content;
    }
}
