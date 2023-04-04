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
use Extedit\Infra\FakeRequest;
use Extedit\Infra\View;
use Extedit\Value\Upload;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ImagePickerTest extends TestCase
{
    private $conf;
    private $imageRepo;
    private $csrfProtector;
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        mkdir("vfs://root/userfiles/images/cmb", 0777, true);
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $this->conf = $plugin_cf['extedit'] + ["editor_external" => "tinymce4"];
        $this->imageRepo = new FakeImageRepo;
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("C241yFT+b4BFU7hhp2oY");
        $this->view = new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["extedit"]);
    }

    private function sut(): ImagePicker
    {
        return new ImagePicker(
            "./",
            "",
            "vfs://root/userfiles/images/",
            $this->conf,
            $this->imageRepo,
            $this->csrfProtector,
            $this->view
        );
    }

    public function testVisitorsCannotAccessImagePicker(): void
    {
        $request = new FakeRequest(["query" => "Extedit"]);
        $response = $this->sut()($request);
        $this->assertEquals("", $response->output());
    }

    public function testShowRendersImagePickerWithNoImages(): void
    {
        $request = new FakeRequest(["query" => "Extedit", "user" => "cmb"]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testShowRendersImagePickerWithImages(): void
    {
        $this->imageRepo->save($this->upload(), "vfs://root/userfiles/images/cmb/image.jpg");
        $this->imageRepo->save($this->upload("png", 480, 640), "vfs://root/userfiles/images/cmb/image.png");
        $request = new FakeRequest(["query" => "Extedit", "user" => "cmb"]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testSuccessfulUploadRedirects(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest(["method" => "post", "user" => "cmb", "upload" => $this->upload()]);
        $response = $this->sut()($request);
        $this->assertNotNull($response->location());
    }

    public function testReportsFailedCsrfCheck(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest(["method" => "post", "user" => "cmb", "upload" => $this->upload()]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testReportsMissingUpload(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest(["method" => "post", "user" => "cmb"]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadFailureShowsError(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "method" => "post",
            "user" => "cmb",
            "query" => "Extedit",
            "upload" => new Upload(['name' => "image.jpg", 'tmp_name' => "does_not_really_matter", 'error' => 1])
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadOfNonImageShowsError(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "method" => "post",
            "user" => "cmb",
            "query" => "Extedit",
            "upload" => new Upload(['name' => "image.txt", 'tmp_name' => "does_not_really_matter", 'error' => 0]),
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testUploadBadFilenameShowsError(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "method" => "post",
            "user" => "cmb",
            "query" => "Extedit",
            "upload" => new Upload(['name' => "äöü.jpg", 'tmp_name' => "does_not_really_matter", 'error' => 0]),
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testMoveUploadFailureShowsError(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "method" => "post",
            "user" => "cmb",
            "query" => "Extedit",
            "upload" => new Upload(["name" => "image.jpg", "tmp_name" => "irrelevant", "error" => 0]),
        ]);
        $response = $this->sut()($request);
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
