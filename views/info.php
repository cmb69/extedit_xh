<?php

use Extedit\View;

/**
 * @var View $this
 * @var string $version
 * @var array<string,string> $checks
 * @var array<string,string> $images
 */
?>
<!-- Extedit: info -->
<h1>Extedit <?=$this->esc($version)?></h1>
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
<ul style="list-style: none">
<?php foreach ($checks as $check => $state):?>
  <li>
    <img src="<?=$this->esc($images[$state])?>" alt="<?=$this->esc($state)?>" style="padding-right: 1em"/>
    <?=$this->esc($check);?>
  </li>
<?php endforeach?>
</ul>
