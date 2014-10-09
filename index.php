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
 * The controller.
 */
require_once $pth['folder']['plugin_classes'] . 'Controller.php';

/**
 * The plugin version.
 */
define('EXTEDIT_VERSION', '@EXTEDIT_VERSION@');

/*
 * For backward compatibility.
 */
if (!defined('XH_ADM')) {
    define('XH_ADM', $adm);
}

/**
 * The controller.
 *
 * @var Extedit_Controller
 */
$_Extedit_controller = new Extedit_Controller();
$_Extedit_controller->dispatch();

/**
 * Returns the view of an extedit.
 *
 * @param string $username The name of the user, who may edit this extedit.
 * @param string $textname The name of the extedit.
 *
 * @return string (X)HTML.
 *
 * @global Extedit_Controller The plugin controller.
 */
function extedit($username, $textname = '')
{
    global $_Extedit_controller;

    return $_Extedit_controller->main($username, $textname);
}


?>
