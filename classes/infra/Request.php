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

class Request
{
    /** @codeCoverageIgnore */
    public static function current(): self
    {
        return new self;
    }

    /** @codeCoverageIgnore */
    public function admin(): bool
    {
        return defined("XH_ADM") && XH_ADM;
    }

    /** @codeCoverageIgnore */
    public function user(): string
    {
        return $_SESSION["username"] ?? "";
    }

    public function action(string $textname): string
    {
        $action = $_GET["extedit_action"] ?? "";
        if (!is_string($action)) {
            return "";
        }
        if (!strncmp($action, "do_", strlen("do_"))) {
            return "";
        }
        if (isset($_POST["extedit_{$textname}_text"])) {
            return "do_$action";
        }
        return $action;
    }
}
