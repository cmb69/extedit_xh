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

use Extedit\ContentRepo;
use Extedit\Dic;
use Extedit\Infra\Request;
use Extedit\Session;
use Extedit\View;

const EXTEDIT_VERSION = '2.0-dev';

/**
 * @param string $username
 * @param string $textname
 * @return string (X)HTML
 */
function extedit($username, $textname = null)
{
    global $pth, $cf, $plugin_cf, $plugin_tx;

    $controller = new Extedit\FunctionController(
        "{$pth['folder']['plugins']}extedit/",
        $pth['folder']['base'],
        $pth['folder']['images'],
        $cf['editor']['external'],
        $plugin_cf['extedit'],
        $plugin_tx['extedit'],
        new ContentRepo("{$pth['folder']['content']}extedit/"),
        new Session(),
        Dic::makeEditor(),
        new View($pth["folder"]["plugins"] . "extedit/views/", $plugin_tx["extedit"]),
        $username,
        $textname
    );
    return $controller->handle(Request::current());
}

/**
 * @var array<string,array<string,string>> $plugin_cf
 */

if ($plugin_cf['extedit']['allow_template'] && ($_GET['extedit_mode'] ?? null) === 'edit') {
    Dic::makeEditor()->init();
}
