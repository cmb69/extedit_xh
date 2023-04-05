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
use Extedit\Infra\ImageRepo;
use Extedit\Infra\Request;
use Extedit\Infra\View;
use Extedit\Value\Html;
use Extedit\Value\Image;
use Extedit\Value\Response;
use Extedit\Value\Upload;

class ImagePicker
{
    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $imageFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var ImageRepo */
    private $imageRepo;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        string $pluginFolder,
        string $imageFolder,
        array $conf,
        ImageRepo $imageRepo,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->imageFolder = $imageFolder;
        $this->conf = $conf;
        $this->imageRepo = $imageRepo;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        if ($request->user() === "") {
            return Response::null();
        }
        switch ($request->method()) {
            default:
                return $this->show($request);
            case "post":
                return $this->upload($request);
        }
    }

    private function show(Request $request): Response
    {
        return $this->respondWith($this->render($request));
    }

    private function render(Request $request, ?string $error = null): string
    {
        return $this->view->render("imagepicker", [
            "images" => $this->imageRecords($this->imageRepo->findAll($this->imageFolder($request))),
            "stylesheet" => $this->pluginFolder . "css/stylesheet.css",
            "script" => $this->pluginFolder . "imagepicker.min.js",
            "editor" => $this->conf["editor_external"],
            "uploadUrl" => $request->url()->with("function", "extedit_imagepicker")->relative(),
            "error" => $error,
            "token" => $this->csrfProtector->token(),
        ]);
    }

    /**
     * @param list<Image> $images
     * @return list<array{title:string,filename:string}>
     */
    private function imageRecords(array $images): array
    {
        return array_map(function (Image $image) {
            $title = basename($image->filename());
            if ($image->width() && $image->height()) {
                $title .= $this->view->plain("imagepicker_dimensions", $image->width(), $image->height());
            }
            return [
                "title" => $title,
                "filename" => $image->filename(),
            ];
        }, $images);
    }

    private function upload(Request $request): Response
    {
        if (!$this->csrfProtector->check()) {
            return $this->respondWith($this->render($request, "err_unauthorized"));
        }
        if (($upload = $request->upload()) === null) {
            return $this->respondWith($this->render($request, "imagepicker_err_nofile"));
        }
        if ($upload->error()) {
            return $this->respondWith($this->render($request, "imagepicker_err_" . $upload->error()));
        }
        if (!$this->imageRepo->isImage($upload->name())) {
            return $this->respondWith($this->render($request, "imagepicker_err_mimetype"));
        }
        if (($destination = $this->sanitizedName($request, $upload)) === "") {
            return $this->respondWith($this->render($request, "imagepicker_err_save"));
        }
        if (!$this->imageRepo->save($upload, $destination)) {
            return $this->respondWith($this->render($request, "imagepicker_err_save"));
        }
        return Response::redirect($request->url()->with("function", "extedit_imagepicker")->absolute());
    }

    private function respondWith(string $output): Response
    {
        return Response::create($output)->withContentType("text/html; charset=utf-8");
    }

    private function sanitizedName(Request $request, Upload $upload): string
    {
        $basename = (string) preg_replace('/[^a-z0-9_.-]/i', "", basename($upload->name()));
        if (!preg_match('/[a-z0-9_-]{1,200}\.[a-z0-9_-]{1,10}/', $basename)) {
            return "";
        }
        return $this->imageFolder($request) . $basename;
    }

    private function imageFolder(Request $request): string
    {
        return $this->conf["images_subfolder"]
            ? $this->imageFolder . preg_replace('/[^a-z0-9-]/i', "", $request->user()) . "/"
            : $this->imageFolder;
    }
}
