<?php

/**
 * The contents.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Extedit
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2013-2016 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Extedit_XH
 */

/**
 * The contents.
 *
 * @category CMSimple_XH
 * @package  Extedit
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Extedit_XH
 */
class Extedit_Content
{
    /**
     * The (X)HTML.
     *
     * @var string
     */
    protected $html;

    /**
     * Returns the path of the content folder. If it doesn't exists, tries to
     * create it.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     */
    public static function getFoldername()
    {
        global $pth;

        $foldername = $pth['folder']['content'] . 'extedit/';
        if (!file_exists($foldername)) {
            mkdir($foldername); // TODO: set permissions; call chmod()
        }
        return $foldername;
    }

    /**
     * Returns the filename of an extedit.
     *
     * @param string $name A name.
     *
     * @return string
     */
    public static function getFilename($name)
    {
        return self::getFoldername() . $name . '.htm';
    }

    /**
     * Finds and returns a content.
     *
     * @param string $name A name.
     *
     * @return string
     */
    public static function find($name)
    {
        $content = new self();
        $filename = self::getFilename($name);
        if (!file_exists($filename)) {
            $content->html = '';
            return $content;
        }
        $html = file_get_contents($filename);
        if ($html !== false) {
            $content->html = $html;
        }
        return $content;
    }

    /**
     * Returns the (X)HTML.
     *
     * @return string (X)HTML.
     */
    public function getHtml()
    {
        return $this->html;
    }
}

?>
