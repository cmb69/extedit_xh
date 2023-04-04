<?php

/**
 * Copyright 2023 Christoph M. Becker
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

namespace Extedit\Infra;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;

class EditorTest extends TestCase
{
    public function testRendersTinyMCE4Script(): void
    {
        global $hjs;
        $sut = new FakeEditor("./", "tinymce4", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $request = new FakeRequest();
        $sut->init($request);
        Approvals::verifyHtml($hjs);
    }

    public function testConfiguresTinyMCE4(): void
    {
        $sut = new FakeEditor("./", "tinymce4", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $request = new FakeRequest();
        $sut->init($request);
        $this->assertStringEqualsFile("./inits/tinymce4.js", $sut->lastConfig());
    }

    public function testRendersCKEditorScript(): void
    {
        global $hjs;
        $sut = new FakeEditor("./", "ckeditor", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $request = new FakeRequest();
        $sut->init($request);
        Approvals::verifyHtml($hjs);
    }

    public function testConfiguresCKEeditor(): void
    {
        $sut = new FakeEditor("./", "ckeditor", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $request = new FakeRequest();
        $sut->init($request);
        $this->assertStringEqualsFile("./inits/ckeditor.js", $sut->lastConfig());
    }

    public function testUnsupportedEditor(): void
    {
        global $hjs;
        $sut = new FakeEditor("./", "unsupported", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $request = new FakeRequest();
        $sut->init($request);
        $this->assertEquals("", $hjs);
        $this->assertFalse($sut->lastConfig());
    }

    public function testInitializesOnlyOnce(): void
    {
        global $hjs;
        $sut = new FakeEditor("./", "tinymce4", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
        $request = new FakeRequest();
        $sut->init($request);
        $hjs = "";
        $sut->init($request);
        $this->assertEquals("", $hjs);
    }
}
