<?php

use Extedit\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $editUrl
 * @var string $textareaName
 * @var string $content
 * @var string $mtimeName
 * @var int $mtime
 */
?>
<!-- extedit edit -->
<a href="<?=$editUrl?>"><?=$this->text('mode_view')?></a>
<form action="" method="POST">
  <textarea name="<?=$textareaName?>" cols="80" rows="25" class="xh-editor" style="width: 100%"><?=$content?></textarea>
  <input type="hidden" name="<?=$mtimeName?>" value="<?=$mtime?>">
</form>
