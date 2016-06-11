<?php

/*
Copyright 2013-2016 Christoph M. Becker

This file is part of Extedit_XH.

Extedit_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Extedit_XH is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Extedit_XH.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Extedit;

class ImagePicker extends AbstractController
{
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
    public function show($message = '')
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
        $bag['message'] = $message;
        return $this->render('imagepicker', $bag);
    }

    public function handleUpload()
    {
        global $plugin_tx;

        $message = '';
        $file = $_FILES['extedit_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $key = $this->getUploadErrorKey($file['error']);
            $message = $plugin_tx['extedit']["imagepicker_err_$key"];
        } else {
            if ($this->isImage($file['tmp_name'])) {
                if (!$this->moveUpload($file)) {
                    $message = $plugin_tx['extedit']["imagepicker_err_cantwrite"];
                }
            } else {
                $message = $plugin_tx['extedit']["imagepicker_err_mimetype"];
            }
        }
        echo $this->show($message);
    }

    private function isImage($filename)
    {
        return strpos($this->getMimeTypeOf($filename), 'image/') === 0;
    }

    private function getMimeTypeOf($filename)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filename);
    }

    private function moveUpload($upload)
    {
        $basename = preg_replace('/[^a-z0-9_.-]/i', '', basename($upload['name']));
        $filename = $this->getImageFolder() . $basename;
        // TODO: process image with GD to avoid dangerous images?
        return move_uploaded_file($upload['tmp_name'], $filename);
    }

    /**
     * Returns the accessible images.
     *
     * @param string $folder
     *
     * @return array
     */
    private function images($folder)
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

    private function getImageFolder()
    {
        global $pth, $plugin_cf;

        $subfolder = $plugin_cf['extedit']['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $this->getCurrentUser())
            : '';
        return rtrim($pth['folder']['images'] . $subfolder, '/') . '/';
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
}
