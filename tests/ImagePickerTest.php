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
use PHPUnit\Framework\MockObject;
use PHPUnit\Framework\TestCase;
use XH\CSRFProtection as CsrfProtector;

class ImagePickerTest extends TestCase
{
    /** @var ImagePicker */
    private $sut;

    /** @var ImageFinder&MockObject */
    private $imageFinder;

    /** @var CsrfProtector&MockObject */
    private $csrfProtector;

    public function setUp(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['extedit'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['extedit'];
        $this->imageFinder = $this->createStub(ImageFinder::class);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="d20386f8f33ff903ebc3680b93f72704">'
        );
        $this->sut = new ImagePicker(
            "./",
            "",
            "",
            "/",
            "whatever",
            $conf,
            $lang,
            "tinymce4",
            $this->imageFinder,
            $this->csrfProtector
        );
    }

    public function testShowRendersImagePickerWithNoImages(): void
    {
        $response = $this->sut->show();
        Approvals::verifyHtml($response->output());
    }

    public function testShowRendersImagePickerWithImages(): void
    {
        $this->imageFinder->method('findAll')->willReturn([
            "image.jpg (640 × 480 px)" => "./userfiles/images/cmb/image.jpg",
            "image.png (480 × 640 px)" => "./userfiles/images/cmb/image.png",
        ]);
        $response = $this->sut->show();
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulUploadRedirects(): void
    {
        $upload = $this->createStub(Upload::class);
        $upload->method('name')->willReturn('image.jpg');
        $upload->method('error')->willReturn(0);
        $upload->method('moveTo')->willReturn(true);
        $response = $this->sut->handleUpload($upload);
        $this->assertNotNull($response->location());
    }

    public function testUploadFailureShowsError(): void
    {
        $upload = new Upload(['name' => "image.jpg", 'tmp_name' => "does_not_really_matter", 'error' => 1]);
        $response = $this->sut->handleUpload($upload);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadOfNonImageShowsError(): void
    {
        $upload = new Upload(['name' => "image.txt", 'tmp_name' => "does_not_really_matter", 'error' => 0]);
        $response = $this->sut->handleUpload($upload);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadBadFilenameShowsError(): void
    {
        $upload = new Upload(['name' => "äöü.jpg", 'tmp_name' => "does_not_really_matter", 'error' => 0]);
        $response = $this->sut->handleUpload($upload);
        Approvals::verifyHtml($response->output());
    }

    public function testMoveUploadFailureShowsError(): void
    {
        $upload = $this->createStub(Upload::class);
        $upload->method('name')->willReturn('image.jpg');
        $upload->method('error')->willReturn(0);
        $upload->method('moveTo')->willReturn(false);
        $response = $this->sut->handleUpload($upload);
        Approvals::verifyHtml($response->output());
    }
}
