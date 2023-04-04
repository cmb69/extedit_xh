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

namespace Extedit\Value;

/**
 * @phpstan-type File array{name: string, tmp_name: string, error: int}
 */
class Upload
{
    /** @var File */
    private $file;

    /** @param File $file*/
    public function __construct($file)
    {
        $this->file = $file;
    }

    public function name(): string
    {
        return $this->file['name'];
    }

    public function error(): int
    {
        return $this->file['error'];
    }

    public function tempName(): string
    {
        return $this->file['tmp_name'];
    }
}
