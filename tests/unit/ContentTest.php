<?php

/**
 * Testing the contents.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Extedit
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2016 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Extedit_XH
 */

namespace Extedit;

require_once './vendor/autoload.php';
require_once './classes/Content.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the contents.
 *
 * @category Testing
 * @package  Extedit
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Extedit_XH
 */
class ContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     */
    public function setUp()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth = ['folder' => ['content' => vfsStream::url('test/')]];
    }

    /**
     * Tests that reading the foldername creates the content folder.
     *
     * @return void
     */
    public function testReadingFoldernameCreatesContentFolder()
    {
        $foldername = Content::getFoldername();
        $this->assertFileExists($foldername);
    }

    /**
     * Tests that the filename is correct.
     *
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
     * Tests that the content is found.
     *
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
     * Tests that a non existing content is empty.
     *
     * @return void
     */
    public function testNonExistingContentIsEmpty()
    {
        $content = Content::find('foo');
        $this->assertEquals('', $content->getHtml());
    }
}
