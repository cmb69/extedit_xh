<?php

use Extedit\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $url
 * @var list<array{string}> $errors
 * @var string $content
 * @var int $mtime
 * @var string $textname
 * @var string $token
 */
?>
<!-- extedit edit -->
<a href="<?=$url?>"><?=$this->text('mode_view')?></a>
<form action="" method="POST">
<?foreach ($errors as $error):?>
  <p class="xh_fail"><?=$this->text(...$error)?></p>
<?endforeach?>
  <textarea name="extedit_text" cols="80" rows="25" class="xh-editor" style="width: 100%"><?=$content?></textarea>
  <input type="hidden" name="extedit_mtime" value="<?=$mtime?>">
  <input type="hidden" name="extedit_do" value="<?=$textname?>">
  <input type="hidden" name="extedit_token" value="<?=$token?>">
</form>
