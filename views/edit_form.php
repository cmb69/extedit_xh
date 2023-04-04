<?php

use Extedit\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $editUrl
 * @var string $content
 * @var int $mtime
 * @var string $textname
 */
?>
<!-- extedit edit -->
<a href="<?=$editUrl?>"><?=$this->text('mode_view')?></a>
<form action="" method="POST">
  <textarea name="extedit_text" cols="80" rows="25" class="xh-editor" style="width: 100%"><?=$content?></textarea>
  <input type="hidden" name="extedit_mtime" value="<?=$mtime?>">
  <input type="hidden" name="extedit_do" value="<?=$textname?>">
</form>
