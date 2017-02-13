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

function Extedit_Tinymce_init()
{
    global $sn, $su;

    if (session_id() == '') {
        session_start();
    }
    $_SESSION['tinymce_fb_callback'] = 'extedit_imagepicker';
    $url = "$sn?$su&extedit_imagepicker";
    return <<<EOS
<script type="text/javascript">
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
</script>
EOS;
}
