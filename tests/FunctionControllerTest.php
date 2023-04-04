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

use ApprovalTests\Approvals;
use Extedit\Infra\CsrfProtector;
use Extedit\Infra\Editor;
use Extedit\Infra\FakeContentRepo;
use Extedit\Infra\FakeRequest;
use Extedit\Infra\Pages;
use Extedit\Infra\View;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FunctionControllerTest extends TestCase
{
    private $conf;
    private $contentRepo;
    private $editor;
    private $csrfProtector;
    private $pages;
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["extedit"];
        $this->contentRepo = new FakeContentRepo("vfs://root/content/extedit/");
        $this->contentRepo->save("test", "some content");
        $this->contentRepo->save("Extedit", "some other content");
        $this->contentRepo->save("plugincall", "{{{trim('something')}}}");
        $this->editor = $this->createStub(Editor::class);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("C241yFT+b4BFU7hhp2oY");
        $this->csrfProtector->method("check")->willReturn(true);
        $this->pages = $this->createStub(Pages::class);
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
    }

    public function sut(): FunctionController
    {
        return new FunctionController(
            $this->conf,
            $this->contentRepo,
            $this->editor,
            $this->csrfProtector,
            $this->pages,
            $this->view
        );
    }

    public function testRendersView(): void
    {
        $request = new FakeRequest(["query" => "Extedit"]);
        $response = $this->sut()($request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersViewForEditors(): void
    {
        $request = new FakeRequest(["query" => "Extedit", "user" => "cmb"]);
        $response = $this->sut()($request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersViewWithImplicitTextname(): void
    {
        $this->pages->method("heading")->willReturn("Extedit");
        $request = new FakeRequest(["query" => "Extedit"]);
        $response = $this->sut()($request, "cmb", null);
        Approvals::verifyHtml($response->output());
    }

    public function testRendersViewWithEvaluatedPluginCalls(): void
    {
        $this->conf = ["allow_scripting" => "true"] + $this->conf;
        $this->pages->method("evaluatePluginCalls")->willReturn("something");
        $request = new FakeRequest(["query" => "Extedit"]);
        $response = $this->sut()($request, "cmb", "plugincall");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersEditor(): void
    {
        $request = new FakeRequest(["query" => "Extedit&extedit_action=edit", "user" => "cmb"]);
        $response = $this->sut()($request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testReportsMissingAuthorizationToEdit(): void
    {
        $request = new FakeRequest(["query" => "Extedit&extedit_action=edit"]);
        $response = $this->sut()($request, "cmb", "test");
        $this->assertEquals("<p class=\"xh_fail\">You are not authorized for this action!</p>\n", $response->output());
    }

    public function testSavesContent(): void
    {
        $request = new FakeRequest([
            "query" => "Extedit&extedit_action=edit",
            "post" => ["extedit_do" => "test", "extedit_text" => "some content", "extedit_mtime" => "0"],
            "user" => "cmb",
        ]);
        $response = $this->sut()($request, "cmb", "test");
        $this->assertEquals("some content", $this->contentRepo->findByName("test"));
        $this->assertEquals("http://example.com/?Extedit&extedit_action=edit", $response->location());
    }

    public function testReportsMissingAuthorizationToSave(): void
    {
        $request = new FakeRequest([
            "query" => "Extedit&extedit_action=edit",
            "post" => ["extedit_do" => "test", "extedit_text" => "some content", "extedit_mtime" => "0"],
        ]);
        $response = $this->sut()($request, "cmb", "test");
        $this->assertEquals("<p class=\"xh_fail\">You are not authorized for this action!</p>\n", $response->output());
    }

    public function testReportsConcurrencyIssue(): void
    {
        $this->contentRepo->options(["lastModification" => 1680563743]);
        $request = new FakeRequest([
            "query" => "Extedit&extedit_action=edit",
            "post" => ["extedit_do" => "test", "extedit_text" => "some content", "extedit_mtime" => "0"],
            "user" => "cmb",
        ]);
        $response = $this->sut()($request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testReportsFailureToSaveContent(): void
    {
        $this->contentRepo->options(["save" => false]);
        $request = new FakeRequest([
            "query" => "Extedit&extedit_action=edit",
            "post" => ["extedit_do" => "test", "extedit_text" => "some content", "extedit_mtime" => "0"],
            "user" => "cmb",
        ]);
        $response = $this->sut()($request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }
}
