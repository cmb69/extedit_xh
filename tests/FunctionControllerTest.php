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
use Extedit\Infra\Request;
use Extedit\Infra\View;
use PHPUnit\Framework\TestCase;

class FunctionControllerTest extends TestCase
{
    private $sut;
    private $contentRepo;
    private $request;

    public function setUp(): void
    {
        $conf = XH_includeVar("./config/config.php", "plugin_cf")["extedit"];
        $this->contentRepo = $this->createStub(ContentRepo::class);
        $this->contentRepo->method("findByName")->willReturn("some content");
        $editor = $this->createStub(Editor::class);
        $view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $this->sut = new FunctionController($conf, $this->contentRepo, $editor, $view);
        $this->request = $this->createStub(Request::class);
    }

    public function testRendersView(): void
    {
        $response = $this->sut->handle($this->request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersViewForEditors(): void
    {
        $this->request->method("user")->willReturn("cmb");
        $response = $this->sut->handle($this->request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersEditor(): void
    {
        $_GET = ["extedit_action" => "edit"];
        $this->request->method("user")->willReturn("cmb");
        $this->request->method("action")->willReturn("edit");
        $response = $this->sut->handle($this->request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testReportsMissingAuthorizationToEdit(): void
    {
        $_GET = ["extedit_action" => "edit"];
        $this->request->method("action")->willReturn("edit");
        $response = $this->sut->handle($this->request, "cmb", "test");
        $this->assertEquals("<p class=\"xh_fail\">You are not authorized for this action!</p>\n", $response->output());
    }

    public function testSavesContent(): void
    {
        $_POST = ["extedit_test_text" => "some content", "extedit_test_mtime" => "0"];
        $this->contentRepo->expects($this->once())->method("save")->willReturn(true);
        $this->request->method("user")->willReturn("cmb");
        $this->request->method("action")->willReturn("do_edit");
        $response = $this->sut->handle($this->request, "cmb", "test");
        $this->assertEquals("http://example.com/?&extedit_action=edit", $response->location());
    }

    public function testReportsMissingAuthorizationToSave(): void
    {
        $_POST = ["extedit_test_text" => "some content", "extedit_test_mtime" => "0"];
        $this->request->method("action")->willReturn("do_edit");
        $response = $this->sut->handle($this->request, "cmb", "test");
        $this->assertEquals("<p class=\"xh_fail\">You are not authorized for this action!</p>\n", $response->output());
    }

    public function testReportsConcurrencyIssue(): void
    {
        $_POST = ["extedit_test_text" => "some content", "extedit_test_mtime" => "0"];
        $this->contentRepo->method("findLastModification")->willReturn(1680563743);
        $this->request->method("user")->willReturn("cmb");
        $this->request->method("action")->willReturn("do_edit");
        $response = $this->sut->handle($this->request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }

    public function testReportsFailureToSaveContent(): void
    {
        $_POST = ["extedit_test_text" => "some content", "extedit_test_mtime" => "0"];
        $this->contentRepo->expects($this->once())->method("save")->willReturn(false);
        $this->contentRepo->method("filename")->willReturn("./content/extedit/test.htm");
        $this->request->method("user")->willReturn("cmb");
        $this->request->method("action")->willReturn("do_edit");
        $response = $this->sut->handle($this->request, "cmb", "test");
        Approvals::verifyHtml($response->output());
    }
}
