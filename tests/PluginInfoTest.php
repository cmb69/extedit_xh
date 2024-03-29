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

use ApprovalTests\Approvals;
use Extedit\Infra\FakeContentRepo;
use Extedit\Infra\SystemChecker;
use Extedit\Infra\View;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class PluginInfoTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        vfsStream::setup("root");
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['extedit'];
        $systemChecker = $this->createStub(SystemChecker::class);
        $systemChecker->method('checkVersion')->willReturn(true);
        $systemChecker->method('checkWritability')->willReturn(true);
        $contentRepo = new FakeContentRepo("vfs://root/content/extedit/");
        $view = new View("./views/", $lang);
        $sut = new PluginInfo("./", $systemChecker, $contentRepo, $view);
        $response = $sut();
        Approvals::verifyHtml($response->output());
    }
}
