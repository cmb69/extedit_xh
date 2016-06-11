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

/*
 * Prevent direct access and usage from unsupported CMSimple_XH versions.
 */
if (!defined('CMSIMPLE_XH_VERSION')
    || strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') !== 0
    || version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.6', 'lt')
) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    die(<<<EOT
Extedit_XH detected an unsupported CMSimple_XH version.
Uninstall Extedit_XH or upgrade to a supported CMSimple_XH version!
EOT
    );
}

define('EXTEDIT_VERSION', '@EXTEDIT_VERSION@');

/**
 * @var Extedit\Controller
 */
$_Extedit_controller = new Extedit\Controller();
$_Extedit_controller->dispatch();

/**
 * @param string $username
 * @param string $textname
 * @return string (X)HTML
 */
function extedit($username, $textname = '')
{
    global $_Extedit_controller;

    return $_Extedit_controller->main($username, $textname);
}
