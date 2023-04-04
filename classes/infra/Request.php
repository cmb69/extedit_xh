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

use Extedit\Value\Upload;
use Extedit\Value\Url;

class Request
{
    /** @codeCoverageIgnore */
    public static function current(): self
    {
        return new self;
    }

    public function url(): Url
    {
        $rest = $this->query();
        if ($rest !== "") {
            $rest = "?" . $rest;
        }
        return Url::from(CMSIMPLE_URL . $rest);
    }

    /** @codeCoverageIgnore */
    public function method(): string
    {
        return strtolower($_SERVER["REQUEST_METHOD"]);
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

    /** @codeCoverageIgnore */
    public function s(): int
    {
        global $s;
        return $s;
    }

    public function action(string $textname): string
    {
        $action = $this->url()->param("extedit_action") ?? "";
        if (!is_string($action)) {
            return "";
        }
        if (!strncmp($action, "do_", strlen("do_"))) {
            return "";
        }
        $post = $this->post();
        if (isset($post["extedit_do"]) && $post["extedit_do"] === $textname) {
            return "do_$action";
        }
        return $action;
    }

    /** @return array{text:string,mtime:string} */
    public function textPost(): array
    {
        return [
            "text" => $this->trimmedPostString("extedit_text"),
            "mtime" => $this->trimmedPostString("extedit_mtime"),
        ];
    }

    private function trimmedPostString(string $name): string
    {
        $post = $this->post();
        if (!isset($post[$name])) {
            return "";
        }
        if (!is_string($post[$name])) {
            return "";
        }
        return trim($post[$name]);
    }

    public function upload(): ?Upload
    {
        if (!isset($_FILES["extedit_file"])) {
            return null;
        }
        return new Upload($_FILES["extedit_file"]);
    }

    /** @codeCoverageIgnore */
    protected function query(): string
    {
        return $_SERVER["QUERY_STRING"];
    }

    /**
     * @return array<string,string|array<string>>
     * @codeCoverageIgnore
     */
    protected function post(): array
    {
        return $_POST;
    }
}
