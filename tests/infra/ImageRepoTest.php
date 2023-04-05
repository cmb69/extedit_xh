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

use Extedit\Value\Image;
use Extedit\Value\Upload;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ImageRepoTest extends TestCase
{
    public function setUp(): void
    {
        vfsStream::setup("root");
    }

    private function sut(): ImageRepo
    {
        return new ImageRepo(XH_includeVar("./config/config.php", "plugin_cf")["extedit"]["images_extensions"]);
    }

    public function testFindsNoImagesInEmptyFolder(): void
    {
        $images = $this->sut()->findAll("vfs://root/");
        $this->assertEmpty($images);
    }

    public function testFindsAllImagesInNonEmptyFolder(): void
    {
        $im = imagecreatetruecolor(50, 50);
        imagejpeg($im, "vfs://root/image1.jpg");
        $im = imagecreatetruecolor(5, 500);
        imagepng($im, "vfs://root/image2.png");
        touch("vfs://root/image3.webp");
        $images = $this->sut()->findAll("vfs://root/");
        $expected = [
            new Image("vfs://root/image1.jpg", 50, 50),
            new Image("vfs://root/image2.png", 5, 500),
            new Image("vfs://root/image3.webp"),
        ];
        $this->assertEquals($expected, $images);
    }

    public function testFailsToSaveIfDestinationExists(): void
    {
        touch("vfs://root/image.jpeg");
        $upload = new Upload(["name" => "irrelevant", "tmp_name" => "irrelevant", "error" => 0]);
        $result = $this->sut()->save($upload, "vfs://root/image.jpeg");
        $this->assertFalse($result);
    }
}
