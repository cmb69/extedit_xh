/*!
 * Copyright 2017-2023 Christoph M. Becker
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

(function () {
    var script;

    script = document.createElement("script");
    script.type = "text/javascript";
    script.src = baseFolder + "plugins/tinymce/tiny_mce/tiny_mce_popup.js";
    document.getElementsByTagName("head")[0].appendChild(script);
}());

function setUrl(url) {
    var win, inputId, input;

    win = tinyMCEPopup.getWindowArg("window");
    inputId = tinyMCEPopup.getWindowArg("input");
    input = win.document.getElementById(inputId);

    input.value = url;
    if (input.onchange) {
        input.onchange();
    }
    tinyMCEPopup.close();
}
