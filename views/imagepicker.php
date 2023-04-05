<?php

use Extedit\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var list<array{title:string,filename:string}> $images
 * @var string $stylesheet
 * @var string $script
 * @var string $editor
 * @var string|null $error
 * @var string $uploadUrl
 * @var string $token
 */
?>
<!DOCTYPE html>
<!-- extedit imagepicker -->
<html>
  <head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <title><?=$this->text('imagepicker_title')?></title>
    <link rel="stylesheet" href="<?=$stylesheet?>" type="text/css">
    <script type="module" src="<?=$script?>"></script>
  </head>
  <body>
<?if (isset($error)):?>
    <div id="message"><p><?=$this->text($error)?></p></div>
<?endif?>
    <div id="imagepicker" data-editor="<?=$editor?>">
<?if (empty($images)):?>
      <p><?=$this->text('imagepicker_empty')?></p>
<?else:?>
<?  foreach ($images as $image):?>
      <img src="<?=$image['filename']?>" alt="<?=$image['title']?>" title="<?=$image['title']?>" data-url="<?=$image['filename']?>"/>
<?  endforeach?>
<?endif?>
    </div>
    <form id="upload" action="<?=$uploadUrl?>" method="post" enctype="multipart/form-data">
      <input type="hidden" name="extedit_token" value="<?=$token?>">
      <input name="extedit_file" type="file">
      <button><?=$this->text('imagepicker_upload')?></button>
    </form>
  </body>
</html>
