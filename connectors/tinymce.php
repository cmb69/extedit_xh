<?php

/**
 * The tinyMCE connector of Extedit_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Extedit
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2013-2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Extedit_XH
 */

/**
 * Returns the JavaScript required for the imagepicker.
 *
 * @return string
 */
function Extedit_Tinymce_init()
{
    if (session_id() == '') {
        session_start();
    }
    $_SESSION['tinymce_fb_callback'] = 'extedit_imagepicker';
    $url = CMSIMPLE_ROOT . '?extedit_imagepicker';
    return <<<EOS
<script type="text/javascript">
/* <![CDATA[[ */
function extedit_imagepicker(field_name, url, type, win) {
    if (type != "image") {
        return false;
    };
    tinyMCE.activeEditor.windowManager.open({
	url: "$url",
	width: 640,
	height: 480,
	resizable: "yes",
	inline: "yes",
	close_previous: "no",
	popup_css : false,
	scrollbars : "yes"
    }, {
	window: win,
	input: field_name
    });
    return false;
}
/* ]]> */
</script>
EOS;
}

?>
