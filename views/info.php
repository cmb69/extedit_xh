<?php $this->preventAccess()?>
<!-- Extedit: info -->
<h1>Extedit &ndash; <?php echo $ptx['label_info']?></h1>
<img src="<?php echo $icon?>" class="extedit_logo" alt="<?php echo
$ptx['alt_logo']?>" />
<p>Version: <?php echo $version?></p>
<p>Copyright &copy; 2013-2016 <a href="http://3-magi.net/">Christoph M.
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
<h4><?php echo $ptx['synopsis'];?></h4>
<p>{{{extedit('<?php echo $ptx['synopsis_username']?>', '<?php echo
$ptx['synopsis_textname']?>');}}}</p>
<dl>
    <dt><?php echo $ptx['synopsis_username']?>:</dt>
    <dd><?php echo $ptx['synopsis_username_desc']?></dd>
    <dt><?php echo $ptx['synopsis_textname']?>:</dt>
    <dd><?php echo $ptx['synopsis_textname_desc']?></dd>
</dl>
<hr />
<h4><?php echo $ptx['syscheck'];?></h4>
<ul style="list-style: none">
<?php foreach ($checks as $check => $state):?>
    <li>
        <img src="<?php echo $images[$state]?>" alt="<?php echo
        $state?>" style="padding-right: 1em"/>
        <?php echo $check;?>
    </li>
<?php endforeach?>
</ul>