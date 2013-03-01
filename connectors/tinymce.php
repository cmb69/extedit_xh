<?php


function extedit_tinymce_init()
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