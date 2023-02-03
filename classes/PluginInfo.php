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

class PluginInfo
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $lang;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var ContentRepo */
    private $contentRepo;

    /** @param array<string,string> $lang */
    public function __construct(
        string $pluginFolder,
        array $lang,
        SystemChecker $systemChecker,
        ContentRepo $contentRepo
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->lang = $lang;
        $this->systemChecker = $systemChecker;
        $this->contentRepo = $contentRepo;
    }

    public function __invoke(): string
    {
        foreach (array('ok', 'warn', 'fail') as $state) {
            $images[$state] = "{$this->pluginFolder}images/$state.png";
        }
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        $data = [
            'images' => $images,
            'checks' => $this->systemChecks(),
            'version' => EXTEDIT_VERSION,
        ];
        return $view->render('info', $data);
    }

    /**
     * @return array<string,string>
     */
    private function systemChecks()
    {
        $phpVersion = '7.1.0';
        $checks = array();
        $checks[sprintf($this->lang['syscheck_phpversion'], $phpVersion)]
            = $this->systemChecker->checkVersion(PHP_VERSION, $phpVersion) ? 'ok' : 'fail';
        foreach (array('fileinfo', 'session') as $ext) {
            $checks[sprintf($this->lang['syscheck_extension'], $ext)]
                = $this->systemChecker->checkExtension($ext) ? 'ok' : 'fail';
        }
        foreach (array('config/', 'languages/') as $folder) {
            $folders[] = $this->pluginFolder . $folder;
        }
        $folders[] = $this->contentRepo->foldername();
        foreach ($folders as $folder) {
            $checks[sprintf($this->lang['syscheck_writable'], $folder)]
                = $this->systemChecker->checkWritability($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }
}
