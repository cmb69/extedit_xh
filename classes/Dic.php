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

use Extedit\Infra\CsrfProtector;

class Dic
{
    public static function makeFunctionController(): FunctionController
    {
        global $pth, $plugin_cf, $plugin_tx;
        return new FunctionController(
            $plugin_cf["extedit"],
            new ContentRepo($pth["folder"]["content"] . "extedit/"),
            Dic::makeEditor(),
            new View($pth["folder"]["plugins"] . "extedit/views/", $plugin_tx["extedit"])
        );
    }

    public static function makeImagePicker(): ImagePicker
    {
        global $pth, $cf, $plugin_cf, $plugin_tx, $sn, $su;
        return new ImagePicker(
            $pth["folder"]["plugins"] . "extedit/",
            $pth["folder"]["base"],
            $pth["folder"]["images"],
            $sn,
            $su,
            $plugin_cf["extedit"],
            $plugin_tx["extedit"],
            $cf["editor"]["external"],
            new ImageFinder($plugin_tx["extedit"]['imagepicker_dimensions']),
            new CsrfProtector
        );
    }

    public static function makePluginInfo(): PluginInfo
    {
        global $pth, $plugin_tx;
        return new PluginInfo(
            $pth["folder"]["plugins"] . "extedit/",
            $plugin_tx["extedit"],
            new SystemChecker(),
            new ContentRepo($pth["folder"]["content"] . "extedit/")
        );
    }

    public static function makeEditor(): Editor
    {
        global $pth, $cf;
        static $instance;
        if (!isset($instance)) {
            $instance = new Editor($pth["folder"]["plugins"] . "extedit/", $cf["editor"]["external"]);
        }
        return $instance;
    }
}
