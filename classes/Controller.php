<?php

/**
 * Copyright 2013-2017 Christoph M. Becker
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

class Controller extends AbstractController
{
    /**
     * @return void
     */
    public function dispatch()
    {
        if ($this->isTemplateEdit()) {
            $this->initEditor();
        }
        if ($this->isAdmin()) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }
    
    private function isTemplateEdit()
    {
        global $plugin_cf;

        return $plugin_cf['extedit']['allow_template']
            && isset($_GET['extedit_mode'])
            && $_GET['extedit_mode'] === 'edit';
    }

    /**
     * @return bool
     */
    private function isAdministrationRequested()
    {
        global $extedit;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('extedit')
            || isset($extedit) && $extedit == 'true';
    }

    /**
     * @return void
     */
    private function handleAdministration()
    {
        global $admin, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                $o .= $this->renderInfo();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    /**
     * @return string (X)HTML
     */
    private function renderInfo()
    {
        global $pth, $plugin_tx;

        foreach (array('ok', 'warn', 'fail') as $state) {
            $images[$state] = "{$pth['folder']['plugins']}extedit/images/$state.png";
        }
        $view = new View("{$pth['folder']['plugins']}extedit/views/", $plugin_tx['extedit']);
        $data = [
            'images' => $images,
            'checks' => $this->systemChecks(),
            'version' => EXTEDIT_VERSION,
        ];
        return $view->render('info', $data);
    }

    /**
     * @return array
     */
    private function systemChecks()
    {
        global $pth, $tx, $plugin_tx;

        $ptx = $plugin_tx['extedit'];
        $phpVersion = '7.1.0';
        $checks = array();
        $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)]
            = version_compare(PHP_VERSION, $phpVersion) >= 0 ? 'ok' : 'fail';
        foreach (array('fileinfo', 'session') as $ext) {
            $checks[sprintf($ptx['syscheck_extension'], $ext)]
                = extension_loaded($ext) ? 'ok' : 'fail';
        }
        $checks[$ptx['syscheck_encoding']]
            = strtoupper($tx['meta']['codepage']) == 'UTF-8' ? 'ok' : 'warn';
        foreach (array('config/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'extedit/' . $folder;
        }
        $folders[] = Content::getFoldername();
        foreach ($folders as $folder) {
            $checks[sprintf($ptx['syscheck_writable'], $folder)]
                = is_writable($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }
}
