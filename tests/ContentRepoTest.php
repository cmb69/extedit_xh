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

class ContentRepoTest extends TestCase
{
    public function testFindsExistingContent(): void
    {
        vfsStream::setup('root/extedit/');
        $folder = vfsStream::url('root/extedit/');
        file_put_contents("{$folder}test.htm", '<p>Some HTML</p>');
        $sut = new ContentRepo($folder);
        $content = $sut->findByName('test');
        $this->assertEquals('<p>Some HTML</p>', $content);
    }

    public function testMissingContentReturnsNullButCreatesFolder(): void
    {
        vfsStream::setup('root/');
        $folder = vfsStream::url('root/extedit/');
        $sut = new ContentRepo($folder);
        $content = $sut->findByName('test');
        $this->assertNull($content);
        $this->assertFileExists(vfsStream::url('root/extedit/'));
    }

    public function testExteditFolderIsCreatedOnAccess(): void
    {
        vfsStream::setup('root/');
        $folder = vfsStream::url('root/extedit/');
        $sut = new ContentRepo($folder);
        $foldername = $sut->foldername();
        $this->assertEquals("vfs://root/extedit/", $foldername);
        $this->assertFileExists(vfsStream::url('root/extedit/'));
    }
}
