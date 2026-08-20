<?php
/*
 * e2menutop.php - the admin area's top menu bar (included by e2header.php).
 */
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="e2menutop">
<tr>
<td class="menutop" nowrap="nowrap"><b>e2</b></td>
<td class="menutop" nowrap="nowrap"><a href="<?php echo $blogfilename; ?>">view blog</a></td>
<td class="menutop" nowrap="nowrap"><a href="e2edit.php">write</a></td>
<?php if ($user_level >= 3) { ?>
<td class="menutop" nowrap="nowrap"><a href="e2categories.php">categories</a></td>
<td class="menutop" nowrap="nowrap"><a href="e2options.php">options</a></td>
<td class="menutop" nowrap="nowrap"><a href="e2template.php">templates</a></td>
<?php } ?>
<?php if ($user_level >= 2) { ?>
<td class="menutop" nowrap="nowrap"><a href="e2team.php">team</a></td>
<?php } ?>
<td class="menutop" nowrap="nowrap"><a href="javascript:profile('<?php echo $user_ID ?>')">profile</a></td>
<td class="menutop" nowrap="nowrap"><a href="e2login.php?action=logout">logout</a></td>
<td class="menutop" nowrap="nowrap" width="100%">&nbsp;</td>
</tr>
</table>