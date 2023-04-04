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

class ImageRepo
{
    /** @return list<Image> */
    public function findAll(string $folder): array
    {
        $images = [];
        if (($dh = opendir($folder)) !== false) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry[0] != '.' && is_file($ffn = $folder . $entry)
                    && is_readable($ffn) && getimagesize($ffn) !== false
                ) {
                    $info = getimagesize($ffn);
                    if ($info) {
                        [$width, $height] = $info;
                    } else {
                        $width = $height = 0;
                    }
                    $images[] = new Image($ffn, $width, $height);
                }
            }
        }
        return $images;
    }

    public function save(Upload $upload, string $destination): bool
    {
        if (file_exists($destination)) {
            return false;
        }
        // TODO: process image with GD to avoid dangerous images?
        return $this->moveUploadedFile($upload->tempName(), $destination);
    }

    /** @codeCoverageIgnore */
    protected function moveUploadedFile(string $from, string $to): bool
    {
        return move_uploaded_file($from, $to);
    }
}
