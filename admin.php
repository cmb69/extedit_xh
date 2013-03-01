<?php

function Extedit_info() // RELEASE-TODO: syscheck
{
    global $pth, $tx, $plugin_tx;

    $ptx = $plugin_tx['extedit'];
    $phpVersion = '4.3.0';
    foreach (array('ok', 'warn', 'fail') as $state) {
        $images[$state] = "{$pth['folder']['plugins']}extedit/images/$state.png";
    }
    $checks = array();
    $checks[sprintf($ptx['syscheck_phpversion'], $phpVersion)] =
        version_compare(PHP_VERSION, $phpVersion) >= 0 ? 'ok' : 'fail';
    foreach (array('pcre', 'session') as $ext) {
	$checks[sprintf($ptx['syscheck_extension'], $ext)]
            = extension_loaded($ext) ? 'ok' : 'fail';
    }
    $checks[$ptx['syscheck_magic_quotes']] =
        !get_magic_quotes_runtime() ? 'ok' : 'fail';
    $checks[$ptx['syscheck_encoding']] =
        strtoupper($tx['meta']['codepage']) == 'UTF-8' ? 'ok' : 'warn';
    foreach (array('config/', 'languages/') as $folder) {
	$folders[] = $pth['folder']['plugins'] . 'extedit/' . $folder;
    }
    $folders[] = Extedit_contentFolder();
    foreach ($folders as $folder) {
	$checks[sprintf($ptx['syscheck_writable'], $folder)] =
            is_writable($folder) ? 'ok' : 'warn';
    }
    $bag = array(
        'ptx' => $ptx,
        'images' => $images,
        'checks' => $checks,
        'icon' => $pth['folder']['plugins'] . 'extedit/extedit.png',
        'version' => EXTEDIT_VERSION
    );
    return Extedit_view('info', $bag);
}

/*
 * Handle plugin administration.
 */
if (isset($extedit) && $extedit == 'true') {
    $o .= print_plugin_admin('off');
    switch ($admin) {
    case '':
        $o .= Extedit_info();
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
