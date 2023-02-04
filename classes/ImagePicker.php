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

use XH\CSRFProtection as CsrfProtector;

class ImagePicker
{
    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $baseFolder;

    /** @var string */
    private $imageFolder;

    /** @var string */
    private $scriptName;

    /** @var string */
    private $selectedUrl;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var string */
    private $configuredEditor;

    /** @var ImageFinder */
    private $imageFinder;

    /**
     * @var CsrfProtector
     */
    private $csrfProtector;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $lang
     */
    public function __construct(
        string $pluginFolder,
        string $baseFolder,
        string $imageFolder,
        string $scriptName,
        string $selectedUrl,
        array $conf,
        array $lang,
        string $configuredEditor,
        ImageFinder $imageFinder,
        CsrfProtector $csrfProtector
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->baseFolder = $baseFolder;
        $this->imageFolder = $imageFolder;
        $this->scriptName = $scriptName;
        $this->selectedUrl = $selectedUrl;
        $this->conf = $conf;
        $this->lang = $lang;
        $this->configuredEditor = $configuredEditor;
        $this->imageFinder = $imageFinder;
        $this->csrfProtector = $csrfProtector;
    }

    public function __destruct()
    {
        $this->csrfProtector->store();
    }

    public function show(): Response
    {
        $response = new Response();
        $response->setHeader("Content-Type", "text/html; charset=utf-8");
        $response->addOuput($this->doShow(""));
        return $response;
    }

    /**
     * @param string $message
     * @return string
     */
    private function doShow($message)
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        $data = [
            'images' => $this->imageFinder->findAll($this->imageFolder),
            'baseFolder' => $this->baseFolder,
            'editorHook' => "{$this->pluginFolder}connectors/{$this->configuredEditor}.js",
            'uploadUrl' => "{$this->scriptName}?{$this->selectedUrl}&extedit_upload",
            'message' => $message,
            'csrfTokenInput' => new HtmlString($this->csrfProtector->tokenInput()),
        ];
        return $view->render('imagepicker', $data);
    }

    /**
     * @return Response
     */
    public function handleUpload()
    {
        $this->csrfProtector->check();
        $response = new Response();
        $file = $_FILES['extedit_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $key = $this->getUploadErrorKey($file['error']);
            $message = $this->lang["imagepicker_err_$key"];
            $response->addOuput($this->doShow($message));
            return $response;
        }
        if (!$this->hasAllowedExtension($file['name'])) {
            $message = $this->lang["imagepicker_err_mimetype"];
            $response->addOuput($this->doShow($message));
            return $response;
        }
        if (!$this->moveUpload($file)) {
            $message = $this->lang["imagepicker_err_cantwrite"];
            $response->addOuput($this->doShow($message));
            return $response;
        }
        $response->redirect(CMSIMPLE_URL . "?{$this->selectedUrl}&extedit_imagepicker");
        return $response;
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function hasAllowedExtension($filename)
    {
        $allowedExtensions = array_map('trim', explode(',', $this->conf['images_extensions']));
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowedExtensions, true);
    }

    /**
     * @param array{name:string,tmp_name:string} $upload
     * @return bool
     */
    private function moveUpload($upload)
    {
        $basename = preg_replace('/[^a-z0-9_.-]/i', '', basename($upload['name']));
        if (!preg_match('/[a-z0-9_-]{1,200}\.[a-z0-9_-]{1,10}/', $basename)) {
            return false;
        }
        $filename = $this->imageFolder . $basename;
        if (file_exists($filename)) {
            return false;
        } else {
            // TODO: process image with GD to avoid dangerous images?
            return move_uploaded_file($upload['tmp_name'], $filename);
        }
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
