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

const CMSIMPLE_URL = "http://example.com/";
const EXTEDIT_VERSION = "2.0-dev";

require_once "../../cmsimple/classes/CSRFProtection.php";
require_once "../../cmsimple/functions.php";

spl_autoload_register(function (string $className) {
    $parts = explode("\\", $className);
    if ($parts[0] !== "Extedit") {
        return;
    }
    if (count($parts) === 3) {
        $parts[1] = strtolower($parts[1]);
    }
    $filename = implode("/", array_slice($parts, 1)) . ".php";
    if (is_readable("./classes/$filename")) {
        include_once "./classes/$filename";
    } elseif (is_readable("./tests/$filename")) {
        include_once "./tests/$filename";
    }
});
