<?php

/**
 * Copyright 2016-2017 Christoph M. Becker
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

class View
{
    /** @var string */
    private $templateFolder;

    /** @var array<string,string> */
    private $lang;

    public function __construct(string $templateFolder, array $lang)
    {
        $this->templateFolder = $templateFolder;
        $this->lang = $lang;
    }

    private $data = array();

    public function __get($name)
    {
        return $this->escape($this->data[$name]);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __call($name, array $args)
    {
        return $this->escape(call_user_func_array($this->data[$name], $args));
    }

    protected function text($key)
    {
        $args = func_get_args();
        array_shift($args);
        return vsprintf($this->lang[$key], $args);
    }

    protected function plural($key, $count)
    {
        if ($count == 0) {
            $key .= '_0';
        } else {
            $key .= XH_numberSuffix($count);
        }
        $args = func_get_args();
        array_shift($args);
        return vsprintf($this->lang[$key], $args);
    }

    public function render(string $_template, array $_data)
    {
        $this->data = $_data;
        ob_start();
        include "{$this->templateFolder}{$_template}.php";
        return ob_get_clean();
    }

    protected function escape($value)
    {
        if (is_scalar($value)) {
            return XH_hsc($value);
        } else {
            return $value;
        }
    }
}
