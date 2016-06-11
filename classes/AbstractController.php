<?php

/*
Copyright 2013-2016 Christoph M. Becker

This file is part of Extedit_XH.

Extedit_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Extedit_XH is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Extedit_XH.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Extedit;

abstract class AbstractController
{
    /**
     * @return string
     */
    protected function getCurrentUser()
    {
        if (session_id() == '') {
            session_start();
        }
        return isset($_SESSION['username'])
            ? $_SESSION['username']
            : '';
    }

    /**
     * @param string $_template
     * @param array  $_bag
     * @return string
     */
    protected function render($_template, $_bag)
    {
        global $pth, $cf;

        $_template = "{$pth['folder']['plugins']}extedit/views/$_template.php";
        $_xhtml = strtolower($cf['xhtml']['endtags']) == 'true';
        unset($pth, $cf);
        extract($_bag);
        ob_start();
        include $_template;
        $view = ob_get_clean();
        if (!$_xhtml) {
            $view = str_replace(' />', '>', $view);
        }
        return $view;
    }

    /**
     * @return void
     */
    protected function preventAccess()
    {
        // do nothing!
    }
}
