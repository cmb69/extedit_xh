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

class ImageFinder
{
    /** @var string */
    private $dimensionFormat;

    public function __construct(string $dimensionFormat)
    {
        $this->dimensionFormat = $dimensionFormat;
    }

    /** @return array<string,string> */
    public function findAll(string $folder): array
    {
        $images = array();
        if (($dh = opendir($folder)) !== false) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry[0] != '.' && is_file($ffn = $folder . $entry)
                    && is_readable($ffn) && getimagesize($ffn) !== false
                ) {
                    $info = getimagesize($ffn);
                    if ($info) {
                        list($width, $height) = $info;
                        $entry .= sprintf($this->dimensionFormat, $width, $height);
                    }
                    $images[$entry] = $ffn;
                }
            }
        }
        return $images;
    }
}
