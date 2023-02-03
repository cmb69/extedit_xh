<?php

/**
 * Copyright 2014-2023 Christoph M. Becker
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

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth = ['folder' => ['content' => vfsStream::url('test/')]];
    }

    /**
     * @return void
     */
    public function testReadingFoldernameCreatesContentFolder()
    {
        $foldername = Content::getFoldername();
        $this->assertFileExists($foldername);
    }

    /**
     * @return void
     */
    public function testFilenameIsCorrect()
    {
        $this->assertEquals(
            vfsStream::url('test/extedit/foo.htm'),
            Content::getFilename('foo')
        );
    }

    /**
     * @return void
     */
    public function testContentIsFound()
    {
        $html = '<p>blah</p>';
        $filename = vfsStream::url('test/extedit/foo.htm');
        mkdir(dirname($filename));
        file_put_contents($filename, $html);
        $content = Content::find('foo');
        $this->assertEquals($html, $content->getHtml());
    }

    /**
     * @return void
     */
    public function testNonExistingContentIsEmpty()
    {
        $content = Content::find('foo');
        $this->assertEquals('', $content->getHtml());
    }
}
