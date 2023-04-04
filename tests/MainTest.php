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

namespace Extedit;

use Extedit\Infra\Editor;
use Extedit\Infra\FakeRequest;
use PHPUnit\Framework\TestCase;

class MainTest extends TestCase
{
    private $conf;
    private $editor;

    public function setUp(): void
    {
        $this->conf = XH_includeVar("./config/config.php", "plugin_cf")["extedit"];
        $this->editor = $this->createStub(Editor::class);
    }

    private function sut(): Main
    {
        return new Main($this->conf, $this->editor);
    }

    public function testInitializesEditor(): void
    {
        $this->conf = ["allow_template" => "true"] + $this->conf;
        $this->editor->expects($this->once())->method("init");
        $request = new FakeRequest(["query" => "&extedit_action=edit"]);
        $this->sut()($request);
    }
}
