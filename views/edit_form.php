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

<a href="<?=$this->escape($editUrl)?>"><?=$this->text('mode_view')?></a>
<form action="" method="POST">
  <textarea name="<?=$this->escape($textareaName)?>" cols="80" rows="25" class="xh-editor" style="width: 100%"><?=$this->escape($content)?></textarea>
  <input type="hidden" name="<?=$this->escape($mtimeName)?>" value="<?=$this->escape($mtime)?>">
</form>
