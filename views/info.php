<?php

use Extedit\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.1 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $version
 * @var list<array{class:string,message:string}> $checks
 */
?>
<!-- extedit info -->
<h1>Extedit <?=$version?></h1>
<h2><?=$this->text('synopsis');?></h2>
<p><code>{{{extedit('<?=$this->text('synopsis_username')?>', '<?=$this->text('synopsis_textname')?>');}}}</code></p>
<p>
  <code><?=$this->text('synopsis_username')?></code>:
  <span><?=$this->text('synopsis_username_desc')?></span>
</p>
<p>
  <code><?=$this->text('synopsis_textname')?></code>:
  <span><?=$this->text('synopsis_textname_desc')?></span>
</p>
<h2><?=$this->text('syscheck');?></h2>
<?foreach ($checks as ["class" => $class, "message" => $message]):?>
  <p class="<?=$class?>"><?=$message?></p>
<?endforeach?>
