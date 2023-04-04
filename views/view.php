<?php

use Extedit\View;

/**
 * @var View $this
 * @var bool $may_edit
 * @var string $url
 * @var string $content
 */
?>
<!-- extedit view -->
<?php if ($may_edit):?>
<a href="<?=$this->esc($url)?>"><?=$this->text('mode_edit')?></a>
<?php endif?>
<?=$this->raw($content)?>
