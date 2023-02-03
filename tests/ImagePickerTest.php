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
use PHPUnit\Framework\TestCase;
use XH\CSRFProtection as CsrfProtector;

class ImagePickerTest extends TestCase
{
    public function testShowRendersImagePicker(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['extedit'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['extedit'];
        $imageFinder = $this->createStub(ImageFinder::class);
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="d20386f8f33ff903ebc3680b93f72704">'
        );
        $sut = new ImagePicker("./", "", "", "/", "whatever", $conf, $lang, "tinymce4", $imageFinder, $csrfProtector);
        $response = $sut->show();
        Approvals::verifyHtml($response->output());
    }
}
