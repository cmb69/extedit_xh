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

class Controller extends AbstractController
{
    /**
     * @return void
     */
    public function dispatch()
    {
        if ($this->getCurrentUser()) {
            if (isset($_GET['extedit_imagepicker'])) {
                $imagePicker = new ImagePicker();
                echo $imagePicker->show();
                exit;
            }
            if (isset($_GET['extedit_upload'])) {
                $imagePicker = new ImagePicker();
                $imagePicker->handleUpload();
                exit;
            }
        }
        if (XH_ADM) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }

    /**
     * @return bool
     */
    protected function isAdministrationRequested()
    {
        global $extedit;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('extedit')
            || isset($extedit) && $extedit == 'true';
    }

    /**
     * @return void
     */
    protected function handleAdministration()
    {
        global $admin, $action, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                $o .= $this->renderInfo();
                break;
            default:
                $o .= plugin_admin_common($action, $admin, 'extedit');
        }
    }

    /**
     * @param string $textname
     * @return int
     */
    protected function mtime($textname)
    {
        $filename = Content::getFilename($textname);
        if (file_exists($filename)) {
            return filemtime($filename);
        } else {
            return 0;
        }
    }

    /**
     * @param string $textname
     * @return string
     */
    protected function read($textname)
    {
        $content = Content::find($textname);
        if ($content->getHtml() !== null) {
            return $content->getHtml();
        } else {
            e('cntopen', 'content', Content::getFilename($textname));
        }
    }

    /**
     * @param string $textname
     * @param string $contents
     * @return void
     */
    protected function write($textname, $contents)
    {
        $filename = Content::getFilename($textname);
        if (file_put_contents($filename, $contents) === false) {
            e('cntsave', 'content', $filename);
        }
    }

    /**
     * @param string $textname
     * @return string
     */
    protected function textname($textname)
    {
        global $h, $s;

        // TODO: check that $s is valid?
        if (empty($textname)) {
            $textname = $h[max($s, 0)];
        }
        $textname = preg_replace('/[^a-z0-9-]/i', '', $textname);
        return $textname;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function evaluatePlugincall($content)
    {
        global $plugin_cf;

        if ($plugin_cf['extedit']['allow_scripting']) {
            $content = evaluate_plugincall($content);
        }
        return $content;
    }

    /**
     * @return void
     * @todo Image picker for other editors
     */
    protected function initEditor()
    {
        global $pth, $hjs, $cf;
        static $again = false;

        if ($again) {
            return;
        }
        $again = true;
        $plugins = $pth['folder']['plugins'];
        $editor = $cf['editor']['external'];
        if (!XH_ADM && in_array($editor, array('tinymce'))) {
            include_once "{$plugins}extedit/connectors/$editor.php";
            $hjs .= extedit_tinymce_init() . "\n";
            $config = file_get_contents("${plugins}extedit/inits/$editor.js");
        } else {
            $config = false;
        }
        init_editor(array('xh-editor'), $config);
    }

    /**
     * @param string $username
     * @param string $textname
     * @return string (X)HTML
     */
    public function main($username, $textname = '')
    {
        global $s, $e, $plugin_tx;

        $ptx = $plugin_tx['extedit'];
        $textname = $this->textname($textname);
        if (!isset($_POST["extedit_${textname}_text"])) {
            $content = $this->read($textname);
        }
        if (XH_ADM || $this->getCurrentUser() == $username) {
            $mtime = $this->mtime($textname);
            if (isset($_POST["extedit_${textname}_text"])) {
                $content = stsl($_POST["extedit_${textname}_text"]);
                if ($_POST["extedit_${textname}_mtime"] >= $mtime) {
                    $this->write($textname, $content);
                    $mtime = time(); // to avoid calling clearstatcache()
                } else {
                    $e .= '<li>' . sprintf($ptx['err_changed'], $textname)
                        . '</li>';
                }
            }
            if (isset($_GET['extedit_mode']) && $_GET['extedit_mode'] === 'edit') {
                $o = a($s, '') . $ptx['mode_view'] . '</a>'
                    . '<form action="" method="POST">'
                    . '<textarea name="extedit_' . $textname . '_text" cols="80"'
                    . ' rows="25" class="xh-editor" style="width: 100%">'
                    . XH_hsc($content)
                    . '</textarea>'
                    . tag(
                        'input type="hidden" name="extedit_' . $textname . '_mtime"'
                        . ' value="' . $mtime . '"'
                    )
                    . '</form>';
                $this->initEditor();
            } else {
                $o = a($s, '&amp;extedit_mode=edit') . $ptx['mode_edit'] . '</a>'
                    . $this->evaluatePlugincall($content);
            }
        } else {
            $o = $this->evaluatePlugincall($content);
        }
        return $o;
    }

    /**
     * @return string (X)HTML
     */
    protected function renderInfo()
    {
        global $pth, $plugin_tx;

        foreach (array('ok', 'warn', 'fail') as $state) {
            $images[$state] = "{$pth['folder']['plugins']}extedit/images/$state.png";
        }
        $bag = array(
            'ptx' => $plugin_tx['extedit'],
            'images' => $images,
            'checks' => $this->systemChecks(),
            'icon' => $pth['folder']['plugins'] . 'extedit/extedit.png',
            'version' => EXTEDIT_VERSION
        );
        return $this->render('info', $bag);
    }

    /**
     * @return array
     */
    protected function systemChecks()
    {
        global $pth, $tx, $plugin_tx;

        $ptx = $plugin_tx['extedit'];
        $phpVersion = '5.3.0';
        $checks = array();
        $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)]
            = version_compare(PHP_VERSION, $phpVersion) >= 0 ? 'ok' : 'fail';
        foreach (array('session') as $ext) {
            $checks[sprintf($ptx['syscheck_extension'], $ext)]
                = extension_loaded($ext) ? 'ok' : 'fail';
        }
        $checks[$ptx['syscheck_magic_quotes']]
            = !get_magic_quotes_runtime() ? 'ok' : 'fail';
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
