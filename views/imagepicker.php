<?php

use Extedit\View;

/**
 * @var View $this
 * @var string $baseFolder
 * @var string $editorHook
 * @var string $message
 * @var string $uploadUrl
 * @var string $csrfTokenInput
 */
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <title><?=$this->text('imagepicker_title')?></title>
    <style type="text/css">
      #message {
        background: #ffa7a7;
      }
      #imagepicker {}
      #imagepicker img {
        float: left;
        max-height: 200px;
        margin: 10px;
        cursor: pointer;
      }
      #upload {
        clear: both;
      }
    </style>
    <script type="text/javascript">
      var baseFolder = "<?=$this->esc($baseFolder)?>";
      function init() {
        var picker = document.getElementById("imagepicker"),
          images = picker.getElementsByTagName("img"),
          i, len;

        function onclick(image) {
          return function() {
            pick(image);
          };
        }

        for (i = 0, len = images.length; i < len; i++) {
          images[i].onclick = onclick(images[i]);
        }
      }

      function pick(image) {
        var re = new RegExp('^' + location.protocol + "//" +
                  location.host + location.pathname),
          path = image.src.replace(re, "./");
        
        setUrl(path);
      }
    </script>
    <script type="text/javascript" src="<?=$this->esc($editorHook)?>"></script>
  </head>
  <body onload="init();">
    <div id="message"><p><?=$this->esc($message)?></p></div>
    <div id="imagepicker">
<?php if (empty($images)):?>
      <p><?=$this->text('imagepicker_empty')?></p>
<?php else:?>
<?php foreach ($images as $image => $url):?>
      <img src="<?=$this->esc($url)?>" alt="<?=$this->esc($image)?>" title="<?=$this->esc($image)?>"/>
<?php endforeach?>
<?php endif?>
    </div>
    <form id="upload" action="<?=$this->esc($uploadUrl)?>" method="POST" enctype="multipart/form-data">
      <?=$this->raw($csrfTokenInput)?>
      <input name="extedit_file" type="file">
      <button><?=$this->text('imagepicker_upload')?></button>
    </form>
  </body>
</html>
