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

namespace Extedit\Infra;

class Editor
{
    /** @var bool */
    private $isEditorInitialized = false;

    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $configuredEditor;

    /** @var array<string,string> */
    private $text;

    /** @param array<string,string> $text */
    public function __construct(string $pluginFolder, string $configuredEditor, array $text)
    {
        $this->pluginFolder = $pluginFolder;
        $this->configuredEditor = $configuredEditor;
        $this->text = $text;
    }

    /**
     * @return void
     * @todo Image picker for other editors
     */
    public function init(Request $request)
    {
        global $hjs;

        if ($this->isEditorInitialized) {
            return;
        }
        $this->isEditorInitialized = true;
        $editor = $this->configuredEditor;
        $init = $this->pluginFolder . "inits/$editor.js";
        if (!$request->admin() && is_readable($init)) {
            if (is_callable([$this, $editor])) {
                $hjs .= $this->$editor($request) . "\n";
                $config = file_get_contents($init);
            } else {
                $config = false;
            }
        } else {
            $config = false;
        }
        $this->initEditor($config);
    }

    private function tinymce4(Request $request): string
    {
        $title = json_encode($this->text["imagepicker_title"]);
        $url = json_encode($request->url()->with("function", "extedit_imagepicker")->relative());
        return <<<EOS
<script>
function extedit_imagepicker(field_name, url, type, win) {
    if (type !== "image") {
        return false;
    };
    tinymce.activeEditor.windowManager.open({
        title: $title,
        url: $url,
        width: 640,
        height: 480,
        inline: 1,
    }, {
        window: win,
        input: field_name
    });
    return false;
}
</script>
EOS;
    }

    private function tinymce5(Request $request): string
    {
        $title = json_encode($this->text["imagepicker_title"]);
        $url = json_encode($request->url()->with("function", "extedit_imagepicker")->relative());
        return <<<EOS
<script>
addEventListener("load", function () {
    tinymce.EditorManager.editors.forEach(function (editor) {
        settings = editor.settings;
        settings.file_picker_callback = extedit_imagepicker;
        editor.destroy();
        tinymce.init(settings);
    });
});
function extedit_imagepicker(callback, value, meta) {
    if (meta.filetype !== "image") {
        return false;
    };
    let dialog =  tinymce.activeEditor.windowManager.openUrl({
        title: $title,
        width: 800,
        url: $url,
        onMessage: function (aDialog, data) {
            if (aDialog === dialog && data.mceAction === "setUrl") {
                callback(data.url);
                dialog.close();
            }
        }
    });
    return false;
}
</script>
EOS;
    }

    private function ckeditor(Request $request): string
    {
        $url = json_encode($request->url()->with("function", "extedit_imagepicker")->relative());
        return <<<EOS
<script>
var extedit_filepicker_url = $url;
</script>
EOS;
    }

    /**
     * @param string|false $config
     * @return void
     * @codeCoverageIgnore
     */
    protected function initEditor($config)
    {
        init_editor(["xh-editor"], $config);
    }
}
