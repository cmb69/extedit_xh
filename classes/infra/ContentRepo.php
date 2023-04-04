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

class ContentRepo
{
    /** @var string */
    private $foldername;

    public function __construct(string $foldername)
    {
        $this->foldername = $foldername;
    }

    public function foldername(): string
    {
        if (!file_exists($this->foldername)) {
            mkdir($this->foldername, 0777, true);
            chmod($this->foldername, 0777);
        }
        return $this->foldername;
    }

    public function filename(string $name): string
    {
        return "{$this->foldername()}{$name}.htm";
    }

    public function findByName(string $name): string
    {
        $filename = "{$this->foldername()}{$name}.htm";
        if (!is_readable($filename)) {
            return "";
        }
        $content = file_get_contents($filename);
        return (string) $content;
    }

    public function save(string $name, string $content): bool
    {
        $filename = "{$this->foldername()}{$name}.htm";
        if (is_file($filename) && !is_writable($filename)) {
            return false;
        }
        return file_put_contents($filename, $content) !== false;
    }

    public function findLastModification(string $name): int
    {
        $filename = "{$this->foldername()}{$name}.htm";
        if (!is_file($filename)) {
            return 0;
        }
        return (int) filemtime($filename);
    }
}
