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
use Extedit\Infra\SystemChecker;
use Extedit\Infra\View;
use Extedit\Value\Response;

class PluginInfo
{
    /** @var string */
    private $pluginFolder;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var ContentRepo */
    private $contentRepo;

    /** @var View */
    private $view;

    public function __construct(
        string $pluginFolder,
        SystemChecker $systemChecker,
        ContentRepo $contentRepo,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->systemChecker = $systemChecker;
        $this->contentRepo = $contentRepo;
        $this->view = $view;
    }

    public function __invoke(): Response
    {
        return Response::create($this->view->render("info", [
            "checks" => $this->systemChecks(),
            "version" => EXTEDIT_VERSION,
        ]));
    }

    /** @return list<array{class:string,message:string}> */
    private function systemChecks(): array
    {
        $checks = [];
        $phpVersion = "7.1.0";
        $state = $this->systemChecker->checkVersion(PHP_VERSION, $phpVersion);
        $checks[] = [
            "class" => "xh_" . ($state ? "success" : "fail"),
            "message" => $this->view->plain("syscheck_phpversion" . ($state ? "" : "_no"), $phpVersion),
        ];
        $xhVersion = "1.7.0";
        $state = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $xhVersion");
        $checks[] = [
            "class" => "xh_" . ($state ? "success" : "fail"),
            "message" => $this->view->plain("syscheck_xhversion" . ($state ? "" : "_no"), $xhVersion),
        ];
        foreach (["config/", "languages/"] as $folder) {
            $folders[] = $this->pluginFolder . $folder;
        }
        $folders[] = $this->contentRepo->foldername();
        foreach ($folders as $folder) {
            $state = $this->systemChecker->checkWritability($folder);
            $checks[] = [
                "class" => "xh_" . ($state ? "success" : "warning"),
                "message" => $this->view->plain("syscheck_writable" . ($state ? "" : "_no"), $folder),
            ];
        }
        return $checks;
    }
}
