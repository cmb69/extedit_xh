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

use Extedit\Infra\ContentRepo;
use Extedit\Infra\CsrfProtector;
use Extedit\Infra\Editor;
use Extedit\Infra\Request;
use Extedit\Infra\View;
use Extedit\Value\Html;
use Extedit\Value\Response;
use Extedit\Value\Url;

class FunctionController
{
    /** @var array<string,string> */
    private $conf;

    /** @var ContentRepo */
    private $contentRepo;

    /** @var Editor */
    private $editor;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        ContentRepo $contentRepo,
        Editor $editor,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->conf = $conf;
        $this->contentRepo = $contentRepo;
        $this->editor = $editor;
        $this->csrfProtector = $csrfProtector;
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
        return Response::create($this->renderView($request->url(), $this->isAuthorizedToEdit($request, $username), $content));
    }

    private function edit(Request $request, string $username, string $textname): Response
    {
        if (!$this->isAuthorizedToEdit($request, $username)) {
            return Response::create($this->view->error("err_unauthorized"));
        }
        $this->editor->init();
        return Response::create($this->renderEditForm($request->url(), $textname, $this->contentRepo->findByName($textname)));
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
        if (!$this->isAuthorizedToEdit($request, $username) || !$this->csrfProtector->check()) {
            return Response::create($this->view->error("err_unauthorized"));
        }
        $post = $request->textPost();
        $mtime = $this->contentRepo->findLastModification($textname);
        if ($post["mtime"] < $mtime) {
            $this->editor->init();
            return Response::create($this->view->error("err_changed", $textname)
                . $this->renderEditForm($request->url(), $textname, $post["text"]));
        }
        if (!$this->contentRepo->save($textname, $post["text"])) {
            $this->editor->init();
            return Response::create($this->view->error("err_save", $this->contentRepo->filename($textname))
                . $this->renderEditForm($request->url(), $textname, $post["text"]));
        }
        return Response::redirect($request->url()->with("extedit_action", "edit")->absolute());
    }

    /**
     * @return string (X)HTML
     */
    private function renderEditForm(Url $url, string $textname, string $content): string
    {
        return $this->view->render('edit_form', [
            'editUrl' => $url->without("extedit_action")->relative(),
            'content' => $content,
            'mtime' => $this->contentRepo->findLastModification($textname),
            "textname" => $textname,
            "token" => $this->csrfProtector->token(),
        ]);
    }

    private function renderView(Url $url, bool $mayEdit, string $content): string
    {
        return $this->view->render("view", [
            "may_edit" => $mayEdit,
            "url" => $url->with("extedit_action", "edit")->relative(),
            "content" => Html::of($this->evaluatePlugincall($content)),
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
