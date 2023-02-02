<!-- Extedit: info -->
<h1>Extedit &ndash; <?=$this->text('label_info')?></h1>
<img src="<?=$this->icon?>" class="extedit_logo" alt="<?=$this->text('alt_logo')?>" />
<p>Version: <?=$this->version?></p>
<p>Copyright &copy; 2013-2017 <a href="http://3-magi.net/">Christoph M.
Becker</a></p>
<p class="extedit_license">This program is free software: you can redistribute
it and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>
<p class="extedit_license">This program is distributed in the hope that it will
be useful, but <em>without any warranty</em>; without even the implied warranty
of <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
the GNU General Public License for more details.</p>
<p class="extedit_license">You should have received a copy of the GNU General
Public License along with this program. If not, see <a
href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.</p>
<hr />
<h4><?=$this->text('synopsis');?></h4>
<p>{{{extedit('<?=$this->text('synopsis_username')?>', '<?=$this->text('synopsis_textname')?>');}}}</p>
<dl>
  <dt><?=$this->text('synopsis_username')?>:</dt>
  <dd><?=$this->text('synopsis_username_desc')?></dd>
  <dt><?=$this->text('synopsis_textname')?>:</dt>
  <dd><?=$this->text('synopsis_textname_desc')?></dd>
</dl>
<hr />
<h4><?=$this->text('syscheck');?></h4>
<ul style="list-style: none">
<?php foreach ($this->checks as $check => $state):?>
  <li>
    <img src="<?=$this->images[$state]?>" alt="<?=$state?>" style="padding-right: 1em"/>
    <?=$check;?>
  </li>
<?php endforeach?>
</ul>
