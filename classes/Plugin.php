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

namespace Extedit;

class Plugin
{
    /**
     * @var bool
     */
    private static $isEditorInitialized = false;

    /**
     * @return void
     */
    public function dispatch()
    {
        if ($this->isTemplateEdit()) {
            $this->initEditor();
        }
        if (defined('XH_ADM') && XH_ADM) {
            XH_registerStandardPluginMenuItems(false);
            if (XH_wantsPluginAdministration('extedit')) {
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
     * @return void
     * @todo Image picker for other editors
     */
    private function initEditor()
    {
        global $pth, $hjs, $cf;

        if (self::$isEditorInitialized) {
            return;
        }
        self::$isEditorInitialized = true;
        $plugins = $pth['folder']['plugins'];
        $editor = $cf['editor']['external'];
        if (!(defined('XH_ADM') && XH_ADM) && in_array($editor, array('ckeditor', 'tinymce', 'tinymce4'))) {
            include_once "{$plugins}extedit/connectors/$editor.php";
            $func = "extedit_{$editor}_init";
            $hjs .= $func() . "\n";
            $config = file_get_contents("{$plugins}extedit/inits/$editor.js");
        } else {
            $config = false;
        }
        init_editor(array('xh-editor'), $config);
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
        global $pth, $plugin_tx;

        $systemChecker = new SystemChecker();
        $ptx = $plugin_tx['extedit'];
        $phpVersion = '7.1.0';
        $checks = array();
        $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)]
            = $systemChecker->checkVersion(PHP_VERSION, $phpVersion) ? 'ok' : 'fail';
        foreach (array('fileinfo', 'session') as $ext) {
            $checks[sprintf($ptx['syscheck_extension'], $ext)]
                = $systemChecker->checkExtension($ext) ? 'ok' : 'fail';
        }
        foreach (array('config/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'extedit/' . $folder;
        }
        $folders[] = Content::getFoldername();
        foreach ($folders as $folder) {
            $checks[sprintf($ptx['syscheck_writable'], $folder)]
                = $systemChecker->checkWritability($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }
}
