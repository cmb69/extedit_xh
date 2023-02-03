<!-- Extedit: info -->
<h1>Extedit <?=$this->escape($version)?></h1>
<h4><?=$this->text('synopsis');?></h4>
<p><code>{{{extedit('<?=$this->text('synopsis_username')?>', '<?=$this->text('synopsis_textname')?>');}}}</code></p>
<p>
  <code><?=$this->text('synopsis_username')?></code>:
  <span><?=$this->text('synopsis_username_desc')?></span>
</p>
<p>
  <code><?=$this->text('synopsis_textname')?></code>:
  <span><?=$this->text('synopsis_textname_desc')?></span>
</p>
<hr />
<h4><?=$this->text('syscheck');?></h4>
<ul style="list-style: none">
<?php foreach ($checks as $check => $state):?>
  <li>
    <img src="<?=$this->escape($images[$state])?>" alt="<?=$this->escape($state)?>" style="padding-right: 1em"/>
    <?=$this->escape($check);?>
  </li>
<?php endforeach?>
</ul>
