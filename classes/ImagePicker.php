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

namespace Extedit;

use XH\CSRFProtection as CsrfProtector;

class ImagePicker extends AbstractController
{
    /**
     * @var CsrfProtector
     */
    private $csrfProtection;

    public function __construct()
    {
        $this->csrfProtection = new CsrfProtector('extedit_csrf_token');
    }

    public function __destruct()
    {
        $this->csrfProtection->store();
    }

    /**
     * @param string $message
     * @return string
     */
    public function show($message = '')
    {
        global $pth, $cf, $plugin_tx, $sn, $su;

        $view = new View("{$pth['folder']['plugins']}extedit/views/", $plugin_tx['extedit']);
        $data = [
            'images' => $this->images($this->getImageFolder()),
            'baseFolder' => $pth['folder']['base'],
            'editorHook' => "{$pth['folder']['plugins']}extedit/connectors/{$cf['editor']['external']}.js",
            'uploadUrl' => "$sn?$su&extedit_upload",
            'message' => $message,
            'csrfTokenInput' => new HtmlString($this->csrfProtection->tokenInput()),
        ];
        header('Content-type: text/html; charset=utf-8');
        return $view->render('imagepicker', $data);
    }

    /**
     * @return string|void
     */
    public function handleUpload()
    {
        global $su, $plugin_tx;

        $this->csrfProtection->check();
        $message = '';
        $file = $_FILES['extedit_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $key = $this->getUploadErrorKey($file['error']);
            $message = $plugin_tx['extedit']["imagepicker_err_$key"];
        } else {
            if ($this->hasAllowedExtension($file['name']) && $this->isImage($file['tmp_name'])) {
                if (!$this->moveUpload($file)) {
                    $message = $plugin_tx['extedit']["imagepicker_err_cantwrite"];
                }
            } else {
                $message = $plugin_tx['extedit']["imagepicker_err_mimetype"];
            }
        }
        if (!$message) {
            header('Location: ' . CMSIMPLE_URL . "?$su&extedit_imagepicker");
            exit;
        } else {
            echo $this->show($message);
        }
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function hasAllowedExtension($filename)
    {
        global $plugin_cf;

        $allowedExtensions = array_map('trim', explode(',', $plugin_cf['extedit']['images_extensions']));
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowedExtensions, true);
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function isImage($filename)
    {
        return strpos(mime_content_type($filename), 'image/') === 0;
    }

    /**
     * @param array $upload
     * @return bool
     */
    private function moveUpload($upload)
    {
        $basename = preg_replace('/[^a-z0-9_.-]/i', '', basename($upload['name']));
        if (!preg_match('/[a-z0-9_-]{1,200}\.[a-z0-9_-]{1,10}/', $basename)) {
            return false;
        }
        $filename = $this->getImageFolder() . $basename;
        if (file_exists($filename)) {
            return false;
        } else {
            // TODO: process image with GD to avoid dangerous images?
            return move_uploaded_file($upload['tmp_name'], $filename);
        }
    }

    /**
     * @param string $folder
     * @return array
     */
    private function images($folder)
    {
        global $plugin_tx;

        $images = array();
        if (($dh = opendir($folder)) !== false) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry[0] != '.' && is_file($ffn = $folder . $entry)
                    && is_readable($ffn) && getimagesize($ffn) !== false
                ) {
                    $info = getimagesize($ffn);
                    if ($info) {
                        list($width, $height) = $info;
                        $entry .= sprintf($plugin_tx['extedit']['imagepicker_dimensions'], $width, $height);
                    }
                    $images[$entry] = $ffn;
                }
            }
        }
        return $images;
    }

    /**
     * @return string
     */
    private function getImageFolder()
    {
        global $pth, $plugin_cf;

        $subfolder = $plugin_cf['extedit']['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $this->getCurrentUser())
            : '';
        return rtrim($pth['folder']['images'] . $subfolder, '/') . '/';
    }

    /**
     * @param int $error
     * @return string
     */
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
