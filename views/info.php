<?php $this->preventAccess()?>
<!-- Extedit: info -->
<h1>Extedit &ndash; <?=$ptx['label_info']?></h1>
<img src="<?=$icon?>" class="extedit_logo" alt="<?=$ptx['alt_logo']?>" />
<p>Version: <?=$version?></p>
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
<h4><?=$ptx['synopsis'];?></h4>
<p>{{{extedit('<?=$ptx['synopsis_username']?>', '<?=$ptx['synopsis_textname']?>');}}}</p>
<dl>
    <dt><?=$ptx['synopsis_username']?>:</dt>
    <dd><?=$ptx['synopsis_username_desc']?></dd>
    <dt><?=$ptx['synopsis_textname']?>:</dt>
    <dd><?=$ptx['synopsis_textname_desc']?></dd>
</dl>
<hr />
<h4><?=$ptx['syscheck'];?></h4>
<ul style="list-style: none">
<?php foreach ($checks as $check => $state):?>
    <li>
        <img src="<?=$images[$state]?>" alt="<?=$state?>" style="padding-right: 1em"/>
        <?=$check;?>
    </li>
<?php endforeach?>
</ul>
