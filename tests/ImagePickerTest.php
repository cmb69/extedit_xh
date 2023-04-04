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
use Extedit\Infra\FakeImageRepo;
use Extedit\Infra\Request;
use Extedit\Infra\View;
use Extedit\Value\Upload;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject;
use PHPUnit\Framework\TestCase;

class ImagePickerTest extends TestCase
{
    /** @var ImagePicker */
    private $sut;

    private $imageRepo;

    /** @var CsrfProtector&MockObject */
    private $csrfProtector;

    public function setUp(): void
    {
        vfsStream::setup("root");
        mkdir("vfs://root/userfiles/images/cmb", 0777, true);
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $conf = $plugin_cf['extedit'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['extedit'];
        $this->imageRepo = new FakeImageRepo;
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="d20386f8f33ff903ebc3680b93f72704">'
        );
        $this->sut = new ImagePicker(
            "./",
            "",
            "vfs://root/userfiles/images/",
            "/",
            "whatever",
            $conf,
            $lang,
            "tinymce4",
            $this->imageRepo,
            $this->csrfProtector,
            new View("./views/", $lang)
        );
    }

    public function testShowRendersImagePickerWithNoImages(): void
    {
        $request = $this->createStub(Request::class);
        $response = $this->sut->show($request);
        Approvals::verifyHtml($response->output());
    }

    public function testShowRendersImagePickerWithImages(): void
    {
        $this->imageRepo->save($this->upload(), "vfs://root/userfiles/images/cmb/image.jpg");
        $this->imageRepo->save($this->upload("png", 480, 640), "vfs://root/userfiles/images/cmb/image.png");
        $request = $this->createStub(Request::class);
        $request->method("user")->willReturn("cmb");
        $response = $this->sut->show($request);
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulUploadRedirects(): void
    {
        $request = $this->createStub(Request::class);
        $response = $this->sut->handleUpload($request, $this->upload());
        $this->assertNotNull($response->location());
    }

    public function testUploadFailureShowsError(): void
    {
        $request = $this->createStub(Request::class);
        $upload = new Upload(['name' => "image.jpg", 'tmp_name' => "does_not_really_matter", 'error' => 1]);
        $response = $this->sut->handleUpload($request, $upload);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadOfNonImageShowsError(): void
    {
        $request = $this->createStub(Request::class);
        $upload = new Upload(['name' => "image.txt", 'tmp_name' => "does_not_really_matter", 'error' => 0]);
        $response = $this->sut->handleUpload($request, $upload);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadBadFilenameShowsError(): void
    {
        $request = $this->createStub(Request::class);
        $upload = new Upload(['name' => "äöü.jpg", 'tmp_name' => "does_not_really_matter", 'error' => 0]);
        $response = $this->sut->handleUpload($request, $upload);
        Approvals::verifyHtml($response->output());
    }

    public function testMoveUploadFailureShowsError(): void
    {
        $request = $this->createStub(Request::class);
        $upload = new Upload(["name" => "image.jpg", "tmp_name" => "irrelevant", "error" => 0]);
        $response = $this->sut->handleUpload($request, $upload);
        Approvals::verifyHtml($response->output());
    }

    private function upload(string $format = "jpg", int $width = 640, int $height = 480): Upload
    {
        assert(in_array($format, ["jpg", "png"]));
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, 0);
        if ($format === "jpg") {
            imagejpeg($image, "vfs://root/image.$format");
        } else {
            imagepng($image, "vfs://root/image.$format");
        }
        return new Upload(["name" => "image.$format", "tmp_name" => "vfs://root/image.$format", "error" => 0]);
    }
}
