<?php

/**
 * Front-end of Extedit_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Extedit
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2013-2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Extedit_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: text/plain; charset=utf-8');
    exit('Access forbidden');
}

/**
 * The plugin version.
 */
define('EXTEDIT_VERSION', '1alpha1');

/*
 * For backward compatibility.
 */
if (!defined('XH_ADM')) {
    define('XH_ADM', $adm);
}

/**
 * Returns the accessible images in the images folder or a subfolder thereof.
 *
 * @param string $subfolder A subfolder path.
 *
 * @return array
 *
 * @global array The paths of system files and folders.
 */
function Extedit_images($subfolder = '')
{
    global $pth;

    $dn = rtrim($pth['folder']['images'] . $subfolder, '/') . '/';
    $images = array();
    if (($dh = opendir($dn)) !== false) {
        while (($fn = readdir($dh)) !== false) {
            if ($fn[0] != '.' && is_file($ffn = $dn . $fn) && is_readable($ffn)
                && getimagesize($ffn) !== false
            ) {
                $images[$fn] = $ffn;
            }
        }
    }
    return $images;
}

/**
 * Returns the path of the content folder. If it doesn't exists, tries to create
 * it. If that fails, an error is reported.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 */
function Extedit_contentFolder()
{
    global $pth;

    $dn = $pth['folder']['content'] . 'extedit/';
    if (!file_exists($dn)) {
        if (!mkdir($dn)) {
            e('cntwriteto', 'folder', $dn);
        }
    }
    return $dn;
}

/**
 * Returns the modification time of an extedit.
 *
 * @param string $textname A text name.
 *
 * @return int
 */
function Extedit_mtime($textname)
{
    $fn = Extedit_contentFolder() . $textname . '.htm';
    if (file_exists($fn)) {
        return filemtime($fn);
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
function Extedit_read($textname)
{
    $fn = Extedit_contentFolder() . $textname . '.htm';
    if (!file_exists($fn)) {
        $contents = '';
    } elseif (($contents = file_get_contents($fn)) === false) {
        e('cntopen', 'content', $fn);
    }
    return $contents;
}

/**
 * Writes contents back to an extedit file. If that fails, an error is reported.
 *
 * @param string $textname A text name.
 * @param string $contents Some contents.
 *
 * @return void
 */
function Extedit_write($textname, $contents)
{
    $fn = Extedit_contentFolder() . $textname . '.htm';
    if (($fp = fopen($fn, 'w')) === false
        || fwrite($fp, $contents) === false
    ) {
        e('cntsave', 'content', $fn);
    }
    if (!empty($fp)) {
        fclose($fp);
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
function Extedit_view($_template, $_bag)
{
    global $pth, $cf;

    $_template = $pth['folder']['plugins'] . 'extedit/views/' . $_template
        . '.htm';
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

/**
 * Returns the (X)HTML document constituting the image picker.
 * If user is not logged in as member, returns FALSE.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the plugins.
 * @global array The localization of the plugins.
 */
function Extedit_imagePicker()
{
    global $pth, $plugin_cf, $plugin_tx;

    $pcf = $plugin_cf['extedit'];
    $ptx = $plugin_tx['extedit'];
    if (session_id() == '') {
        session_start();
    }
    if (empty($_SESSION['username'])) {
        return false;
    } else {
        header('Content-type: text/html; charset=utf-8');
        $images = $pcf['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $_SESSION['username'])
            : '';
        $bag['images'] = Extedit_images($images);
        $bag['title'] = $ptx['imagepicker_title'];
        $bag['no_images'] = $ptx['imagepicker_empty'];
        $bag['tinymce_popup'] = $pth['folder']['plugins']
            . 'tinymce/tiny_mce/tiny_mce_popup.js';
        return Extedit_view('imagepicker', $bag);
    }
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
 * @global int   The number of pages.
 */
function Extedit_textname($textname)
{
    global $h, $s, $cl;

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
function Extedit_evaluated($content)
{
    global $plugin_cf;

    $pcf = $plugin_cf['extedit'];
    if ($pcf['allow_scripting'] && function_exists('evaluate_plugincall')) {
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
 * @todo: image picker for other editors
 */
function Extedit_initEditor()
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
        include_once "${plugins}extedit/connectors/$editor.php";
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
function extedit($username, $textname = '')
{
    global $s, $e, $plugin_tx;

    $ptx = $plugin_tx['extedit'];
    $textname = Extedit_textname($textname);
    if (!isset($_POST["extedit_${textname}_text"])) {
        $content = Extedit_read($textname);
    }
    if (session_id() == '') {
        session_start();
    }
    if (XH_ADM
        || isset($_SESSION['username']) && $username == $_SESSION['username']
    ) {
        $mtime = Extedit_mtime($textname);
        if (isset($_POST["extedit_${textname}_text"])) {
            $content = stsl($_POST["extedit_${textname}_text"]);
            if ($_POST["extedit_${textname}_mtime"] >= $mtime) {
                Extedit_write($textname, $content);
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
                . htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
                . '</textarea>'
                . tag(
                    'input type="hidden" name="extedit_' . $textname . '_mtime"'
                    . ' value="' . $mtime . '"'
                )
                . '</form>';
            Extedit_initEditor();
        } else {
            $o = a($s, '&amp;extedit_mode=edit') . $ptx['mode_edit'] . '</a>'
                . Extedit_evaluated($content);
        }
    } else {
        $o = Extedit_evaluated($content);
    }
    return $o;
}

/*
 * Handle request for image picker.
 */
if (isset($_GET['extedit_imagepicker'])) {
    echo Extedit_imagePicker();
    exit;
}

?>
