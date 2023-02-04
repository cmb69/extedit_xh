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
     * @return void
     */
    public function dispatch()
    {
        if ($this->isTemplateEdit()) {
            Dic::makeEditor()->init();
        }
        if (defined('XH_ADM') && XH_ADM) {
            XH_registerStandardPluginMenuItems(false);
            if (XH_wantsPluginAdministration('extedit')) {
                $this->handleAdministration();
            }
        }
    }

    private function isTemplateEdit(): bool
    {
        global $plugin_cf;

        return $plugin_cf['extedit']['allow_template']
            && isset($_GET['extedit_mode'])
            && $_GET['extedit_mode'] === 'edit';
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
                $o .= Dic::makePluginInfo()();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }
}
