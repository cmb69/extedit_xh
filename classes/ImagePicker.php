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

use Extedit\Value\Response;
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
        return Response::create($this->doShow(""))->withHeader("Content-Type", "text/html; charset=utf-8");
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
            'uploadUrl' => "{$this->scriptName}?{$this->selectedUrl}&extedit_imagepicker=upload",
            'message' => $message,
            'csrfTokenInput' => $this->csrfProtector->tokenInput(),
        ];
        return $view->render('imagepicker', $data);
    }

    public function handleUpload(Upload $upload): Response
    {
        $this->csrfProtector->check();
        if ($upload->error()) {
            $key = $this->getUploadErrorKey($upload->error());
            $message = $this->lang["imagepicker_err_$key"];
            return Response::create($this->doShow($message));
        }
        if (!$this->hasAllowedExtension($upload->name())) {
            $message = $this->lang["imagepicker_err_mimetype"];
            return Response::create($this->doShow($message));
        }
        $destination = $this->sanitizedName($upload);
        if ($destination === "") {
            $message = $this->lang["imagepicker_err_cantwrite"];
            return Response::create($this->doShow($message));
        }
        if (!$upload->moveTo($destination)) {
            $message = $this->lang["imagepicker_err_cantwrite"];
            return Response::create($this->doShow($message));
        }
        return Response::redirect(CMSIMPLE_URL . "?{$this->selectedUrl}&extedit_imagepicker");
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

    private function sanitizedName(Upload $upload): string
    {
        $basename = preg_replace('/[^a-z0-9_.-]/i', '', basename($upload->name()));
        if (!preg_match('/[a-z0-9_-]{1,200}\.[a-z0-9_-]{1,10}/', $basename)) {
            return "";
        }
        return $this->imageFolder . $basename;
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
