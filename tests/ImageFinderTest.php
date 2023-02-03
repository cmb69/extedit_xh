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

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ImageFinderTest extends TestCase
{
    public function testFindsNoImagesInEmptyFolder(): void
    {
        vfsStream::setup("root/");
        $sut = new ImageFinder(" (%1\$d × %2\$d px)");
        $images = $sut->findAll(vfsStream::url("root/"));
        $this->assertEmpty($images);
    }

    public function testFindsAllImagesInNonEmptyFolder(): void
    {
        vfsStream::setup("root/");
        $im = imagecreatetruecolor(50, 50);
        imagejpeg($im, vfsStream::url("root/image.jpg"));
        $im = imagecreatetruecolor(5, 500);
        imagejpeg($im, vfsStream::url("root/image.png"));
        // touch(vfsStream::url("root/text.txt"));
        $sut = new ImageFinder(" (%1\$d × %2\$d px)");
        $images = $sut->findAll(vfsStream::url("root/"));
        $expected = [
            "image.jpg (50 × 50 px)" => "vfs://root/image.jpg",
            "image.png (5 × 500 px)" => "vfs://root/image.png",
        ];
        $this->assertEquals($expected, $images);
    }
}
