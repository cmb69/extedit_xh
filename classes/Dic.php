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

use Extedit\Infra\ContentRepo;
use Extedit\Infra\CsrfProtector;
use Extedit\Infra\Editor;
use Extedit\Infra\ImageRepo;
use Extedit\Infra\Pages;
use Extedit\Infra\SystemChecker;
use Extedit\Infra\View;

class Dic
{
    public static function makeMain(): Main
    {
        global $plugin_cf;
        return new Main($plugin_cf["extedit"], self::makeEditor());
    }

    public static function makeFunctionController(): FunctionController
    {
        global $pth, $plugin_cf;
        return new FunctionController(
            $plugin_cf["extedit"],
            new ContentRepo($pth["folder"]["content"] . "extedit/"),
            Dic::makeEditor(),
            new CsrfProtector,
            new Pages,
            self::makeView()
        );
    }

    public static function makeImagePicker(): ImagePicker
    {
        global $pth, $cf, $plugin_cf;
        return new ImagePicker(
            $pth["folder"]["plugins"] . "extedit/",
            $pth["folder"]["images"],
            $plugin_cf["extedit"] + ["editor_external" => $cf["editor"]["external"]],
            new ImageRepo($plugin_cf["extedit"]["images_extensions"]),
            new CsrfProtector,
            self::makeView()
        );
    }

    public static function makePluginInfo(): PluginInfo
    {
        global $pth;
        return new PluginInfo(
            $pth["folder"]["plugins"] . "extedit/",
            new SystemChecker,
            new ContentRepo($pth["folder"]["content"] . "extedit/"),
            self::makeView()
        );
    }

    private static function makeEditor(): Editor
    {
        global $pth, $cf, $plugin_tx;
        static $instance;
        if (!isset($instance)) {
            $instance = new Editor(
                $pth["folder"]["plugins"] . "extedit/",
                $cf["editor"]["external"],
                $plugin_tx["extedit"]
            );
        }
        return $instance;
    }

    private static function makeView(): View
    {
        global $pth, $plugin_tx;
        return new View($pth["folder"]["plugins"] . "extedit/views/", $plugin_tx["extedit"]);
    }
}
