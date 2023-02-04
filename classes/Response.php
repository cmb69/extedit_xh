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

class Response
{
    /** @var string */
    private $output = "";

    /** @var array<string,string> */
    private $headers = [];

    /** @var string|null */
    private $location = null;

    /** @return void */
    public function addOuput(string $output)
    {
        $this->output .= $output;
    }

    /** @return void */
    public function setHeader(string $key, string $value)
    {
        $this->headers[$key] = $value;
    }

    /** @return void */
    public function redirect(string $location)
    {
        $this->location = $location;
    }

    /** @return string|never */
    public function trigger()
    {
        if ($this->location !== null) {
            header("Location: $this->location");
            exit;
        }
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
        return $this->output;
    }

    public function output(): string
    {
        return $this->output;
    }
}
