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
use Extedit\Infra\Pages;
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

    /** @var Pages */
    private $pages;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        ContentRepo $contentRepo,
        Editor $editor,
        CsrfProtector $csrfProtector,
        Pages $pages,
        View $view
    ) {
        $this->conf = $conf;
        $this->contentRepo = $contentRepo;
        $this->editor = $editor;
        $this->csrfProtector = $csrfProtector;
        $this->pages = $pages;
        $this->view = $view;
    }

    public function __invoke(Request $request, string $username, ?string $textname): Response
    {
        $textname = $this->sanitizeTextname($request, $textname);
        switch ($request->action($textname)) {
            default:
                return $this->view($request, $username, $textname);
            case "edit":
                return $this->edit($request, $username, $textname);
            case "do_edit":
                return $this->save($request, $username, $textname);
        }
    }

    private function sanitizeTextname(Request $request, ?string $textname): string
    {
        if ($textname === null) {
            $textname = $this->pages->heading(max(0, $request->s()));
        }
        return (string) preg_replace('/[^a-z0-9-]/i', "", $textname);
    }

    private function view(Request $request, string $username, string $textname): Response
    {
        $mayEdit = $this->isAuthorizedToEdit($request, $username);
        $content = $this->contentRepo->findByName($textname);
        return Response::create($this->renderView($request->url(), $mayEdit, $content));
    }

    private function edit(Request $request, string $username, string $textname): Response
    {
        if (!$this->isAuthorizedToEdit($request, $username)) {
            return Response::create($this->view->error("err_unauthorized"));
        }
        $content = $this->contentRepo->findByName($textname);
        $this->editor->init($request);
        return Response::create($this->renderEditForm($request->url(), $textname, $content));
    }

    private function save(Request $request, string $username, string $textname): Response
    {
        if (!$this->isAuthorizedToEdit($request, $username) || !$this->csrfProtector->check()) {
            return Response::create($this->view->error("err_unauthorized"));
        }
        $post = $request->textPost();
        $mtime = $this->contentRepo->findLastModification($textname);
        if ((int) $post["mtime"] < $mtime) {
            $errors = [["err_changed", $textname]];
            $this->editor->init($request);
            return Response::create($this->renderEditForm($request->url(), $textname, $post["text"], $errors));
        }
        if (!$this->contentRepo->save($textname, $post["text"])) {
            $errors = [["err_save", $this->contentRepo->filename($textname)]];
            $this->editor->init($request);
            return Response::create($this->renderEditForm($request->url(), $textname, $post["text"], $errors));
        }
        return Response::redirect($request->url()->with("extedit_action", "edit")->absolute());
    }

    private function isAuthorizedToEdit(Request $request, string $username): bool
    {
        return $request->admin()
            || $request->user() && $username == "*"
            || $request->user() && in_array($request->user(), explode(",", $username), true);
    }

    private function renderView(Url $url, bool $mayEdit, string $content): string
    {
        return $this->view->render("view", [
            "may_edit" => $mayEdit,
            "url" => $url->with("extedit_action", "edit")->relative(),
            "content" => Html::of($this->evaluatePlugincall($content)),
        ]);
    }

    /** @param list<array{string}> $errors */
    private function renderEditForm(Url $url, string $textname, string $content, array $errors = []): string
    {
        return $this->view->render("edit_form", [
            "url" => $url->without("extedit_action")->relative(),
            "errors" => $errors,
            "content" => $content,
            "mtime" => $this->contentRepo->findLastModification($textname),
            "textname" => $textname,
            "token" => $this->csrfProtector->token(),
        ]);
    }

    private function evaluatePlugincall(string $content): string
    {
        if ($this->conf["allow_scripting"]) {
            return $this->pages->evaluatePluginCalls($content);
        }
        return $content;
    }
}
