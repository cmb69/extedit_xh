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

use Extedit\Infra\CsrfProtector;
use Extedit\Infra\Request;
use Extedit\Infra\View;
use Extedit\Value\Html;
use Extedit\Value\Response;

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

    /** @var View */
    private $view;

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
        CsrfProtector $csrfProtector,
        View $view
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
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        if (isset($_GET['extedit_imagepicker']) && $request->user() !== "") {
            if ($_GET['extedit_imagepicker'] !== "upload") {
                return $this->show($request);
            }
            if ($_GET['extedit_imagepicker'] === "upload") {
                return $this->handleUpload($request, new Upload($_FILES['extedit_file']));
            }
        }
        return Response::create("");
    }

    public function show(Request $request): Response
    {
        return Response::create($this->doShow($request, ""))->withContentType("text/html; charset=utf-8");
    }

    /**
     * @param string $message
     * @return string
     */
    private function doShow(Request $request, $message)
    {
        $data = [
            'images' => $this->imageFinder->findAll($this->getImageFolder($request)),
            'baseFolder' => $this->baseFolder,
            'editorHook' => "{$this->pluginFolder}connectors/{$this->configuredEditor}.js",
            'uploadUrl' => "{$this->scriptName}?{$this->selectedUrl}&extedit_imagepicker=upload",
            'message' => $message,
            'csrfTokenInput' => Html::of($this->csrfProtector->tokenInput()),
        ];
        $this->csrfProtector->store();
        return $this->view->render('imagepicker', $data);
    }

    public function handleUpload(Request $request, Upload $upload): Response
    {
        $this->csrfProtector->check();
        if ($upload->error()) {
            $key = $this->getUploadErrorKey($upload->error());
            $message = $this->lang["imagepicker_err_$key"];
            return Response::create($this->doShow($request, $message))->withContentType("text/html; charset=utf-8");
        }
        if (!$this->hasAllowedExtension($upload->name())) {
            $message = $this->lang["imagepicker_err_mimetype"];
            return Response::create($this->doShow($request, $message))->withContentType("text/html; charset=utf-8");
        }
        $destination = $this->sanitizedName($request, $upload);
        if ($destination === "") {
            $message = $this->lang["imagepicker_err_cantwrite"];
            return Response::create($this->doShow($request, $message))->withContentType("text/html; charset=utf-8");
        }
        if (!$upload->moveTo($destination)) {
            $message = $this->lang["imagepicker_err_cantwrite"];
            return Response::create($this->doShow($request, $message))->withContentType("text/html; charset=utf-8");
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

    private function sanitizedName(Request $request, Upload $upload): string
    {
        $basename = preg_replace('/[^a-z0-9_.-]/i', '', basename($upload->name()));
        if (!preg_match('/[a-z0-9_-]{1,200}\.[a-z0-9_-]{1,10}/', $basename)) {
            return "";
        }
        return $this->getImageFolder($request) . $basename;
    }

    /**
     * @return string
     */
    private function getImageFolder(Request $request)
    {
        $subfolder = $this->conf['images_subfolder']
            ? preg_replace('/[^a-z0-9-]/i', '', $request->user())
            : '';
        return rtrim($this->imageFolder . $subfolder, '/') . '/';
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
