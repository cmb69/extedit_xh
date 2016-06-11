<?php

/**
 * The extedit controllers.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Extedit
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2013-2016 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Extedit_XH
 */

namespace Extedit;

/**
 * The extedit controllers.
 *
 * @category CMSimple_XH
 * @package  Extedit
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Extedit_XH
 */
class Controller
{
    /**
     * Dispatches on plugin related requests.
     *
     * @return void
     */
    public function dispatch()
    {
        if ($this->getCurrentUser()) {
            if (isset($_GET['extedit_imagepicker'])) {
                echo $this->imagePicker();
                exit;
            }
            if (isset($_GET['extedit_upload'])) {
                $this->handleUpload();
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

    private function handleUpload()
    {
        global $plugin_tx;

        $file = $_FILES['extedit_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $key = $this->getUploadErrorKey($file['error']);
            echo $plugin_tx['extedit']["imagepicker_err_$key"];
        } else {
            $basename = preg_replace('/[^a-z0-9_.-]/i', '', basename($file['name']));
            $filename = $this->getImageFolder() . $basename;
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (strpos($mimeType, 'image/') === 0) {
                // TODO: process image with GD to avoid dangerous images?
                if (!move_uploaded_file($file['tmp_name'], $filename)) {
                    echo $plugin_tx['extedit']["imagepicker_err_cantwrite"];
                }
            } else {
                echo $plugin_tx['extedit']["imagepicker_err_mimetype"];
            }
        }
        echo $this->imagePicker();
    }

    private function getUploadErrorKey($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'inisize';
            case UPLOAD_ERR_FORM_SIZE:
                return 'formsize';
            case UPLOAD_ERR_PARTIAL:
                return 'partial';
            case UPLOAD_ERR_NO_FILE:
                return 'nofile';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'notmpdir';
            case UPLOAD_ERR_CANT_WRITE:
                return 'cantwrite';
            case UPLOAD_ERR_EXTENSION:
                return 'extension';
            default:
                return 'unknown';
        }
    }

    /**
     * Returns whether the plugin administration is requested.
     *
     * @return bool
     *
     * @global string Whether the query parameter <var>extedit</var> is set.
     */
    protected function isAdministrationRequested()
    {
        global $extedit;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('extedit')
            || isset($extedit) && $extedit == 'true';
    }

    /**
     * Handles the plugin administration.
     *
     * @return void
     *
     * @global string The value of the <var>admin</var> GP parameter.
     * @global string The value of the <var>action</var> GP parameter.
     * @global string The (X)HTML of the contents area.
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
     * Returns the accessible images.
     *
     * @param string $folder
     *
     * @return array
     */
    protected function images($folder)
    {
        $images = array();
        if (($dh = opendir($folder)) !== false) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry[0] != '.' && is_file($ffn = $folder . $entry)
                    && is_readable($ffn) && getimagesize($ffn) !== false
                ) {
                    $images[$entry] = $ffn;
                }
            }
        }
        return $images;
    }

    /**
     * Returns the modification time of an extedit.
     *
     * @param string $textname A text name.
     *
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
     * Returns the content of an extedit file. If the file doesn't exist, returns
     * an empty string. If the file can't be read, an error is reported.
     *
     * @param string $textname A text name.
     *
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
     * Writes contents back to an extedit file. If that fails, an error is reported.
     *
     * @param string $textname A text name.
     * @param string $contents Some contents.
     *
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
     * Returns an instantiated view template.
     *
     * @param string $_template The path of the template file.
     * @param array  $_bag      The variables.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
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

    private function preventAccess()
    {
        // do nothing!
    }

    /**
     * Returns the (X)HTML document constituting the image picker.
     * If user is not logged in as member, returns FALSE.
     *
     * @return string
     *
     * @global array  The paths of system files and folders.
     * @global array  The localization of the plugins.
     * @global string The site name.
     */
    protected function imagePicker()
    {
        global $pth, $plugin_tx, $sn;

        $ptx = $plugin_tx['extedit'];
        header('Content-type: text/html; charset=utf-8');
        $bag['images'] = $this->images($this->getImageFolder());
        $bag['title'] = $ptx['imagepicker_title'];
        $bag['no_images'] = $ptx['imagepicker_empty'];
        $bag['tinymce_popup'] = $pth['folder']['plugins']
            . 'tinymce/tiny_mce/tiny_mce_popup.js';
        $bag['upload_url'] = "$sn?&extedit_upload";
        $bag['upload'] = $ptx['imagepicker_upload'];
        return $this->render('imagepicker', $bag);
    }

    private function getImageFolder()
    {
        global $pth, $plugin_cf;
        
        $subfolder = $plugin_cf['extedit']['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $this->getCurrentUser())
            : '';
        return rtrim($pth['folder']['images'] . $subfolder, '/') . '/';
    }

    /**
     * Returns the sanitized text name. If $textname is empty, returns the sanitized
     * heading of the current page.
     *
     * @param string $textname A text name.
     *
     * @return string
     *
     * @global array The headings of the pages.
     * @global int   The current page index.
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
     * Returns the content with plugin calls evaluated, if allowed in the
     * configuration.
     *
     * @param string $content A content.
     *
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
     * Initializes the configured editor for extedit. Makes available an image
     * picker, if the editor is tinyMCE.
     *
     * @return void
     *
     * @global array  The paths of system files and folders.
     * @global string (X)HTML to insert into the `head' element.
     * @global array  The configuration of the system core.
     *
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
     * Returns the view of an extedit.
     *
     * @param string $username The name of the user, who may edit this extedit.
     * @param string $textname The name of the extedit.
     *
     * @return string (X)HTML.
     *
     * @global int    The current page index.
     * @global string The list of error messages.
     * @global array  The localization of the plugins.
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
     * Returns the plugin information view.
     *
     * @return string  The (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the plugins.
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
     * Returns the system checks.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the core.
     * @global array The localization of the plugins.
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

    /**
     * Returns the current user.
     *
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
}
