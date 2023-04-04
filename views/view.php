<?php

use Extedit\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var bool $may_edit
 * @var string $url
 * @var string $content
 */
?>
<!-- extedit view -->
<?if ($may_edit):?>
<a href="<?=$url?>"><?=$this->text('mode_edit')?></a>
<?endif?>
<?=$content?>
