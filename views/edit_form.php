<?php

use Extedit\View;

/**
 * @var View $this
 * @var string $editUrl
 * @var string $textareaName
 * @var string $content
 * @var string $mtimeName
 * @var int $mtime
 */
?>

<a href="<?=$this->esc($editUrl)?>"><?=$this->text('mode_view')?></a>
<form action="" method="POST">
  <textarea name="<?=$this->esc($textareaName)?>" cols="80" rows="25" class="xh-editor" style="width: 100%"><?=$this->esc($content)?></textarea>
  <input type="hidden" name="<?=$this->esc($mtimeName)?>" value="<?=$this->esc($mtime)?>">
</form>
