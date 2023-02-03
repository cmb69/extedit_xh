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

class Content
{
    /**
     * @var string
     */
    protected $html;

    /**
     * @return string
     */
    public static function getFoldername()
    {
        global $pth;

        $foldername = $pth['folder']['content'] . 'extedit/';
        if (!file_exists($foldername)) {
            mkdir($foldername);
            chmod($foldername, 0777);
        }
        return $foldername;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getFilename($name)
    {
        return self::getFoldername() . $name . '.htm';
    }

    /**
     * @param string $name
     * @return self
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
     * @return string (X)HTML.
     */
    public function getHtml()
    {
        return $this->html;
    }
}
