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

class Session
{
    /**
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        XH_startSession();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value)
    {
        XH_startSession();
        $_SESSION[$key] = $value;
    }
}
