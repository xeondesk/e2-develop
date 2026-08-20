<?php
/*
 * e2edit.showposts.php - lists the posts for editing/deletion.
 */

$showposts = 20;
$query = "SELECT ID, post_author, post_date, post_title, post_category FROM $tableposts ORDER BY post_date DESC LIMIT $showposts";
$result = mysql_query($query);

echo $tabletop;
?>
<p><b>Edit / delete posts</b> <i>(the <?php echo $showposts ?> most recent)</i></p>
<table width="100%" cellpadding="3" cellspacing="0">
<tr>
<td class="tabletoprow"><b>Date</b></td>
<td class="tabletoprow"><b>Title</b></td>
<td class="tabletoprow"><b>Category</b></td>
<td class="tabletoprow"><b>Author</b></td>
<td class="tabletoprow" colspan="2"><b>Actions</b></td>
</tr>
<?php
while ($row = mysql_fetch_object($result)) {
	$postdata = get_postdata($row->ID);
	$authordata = get_userdata($row->post_author);
	$title = ($row->post_title != '') ? $row->post_title : '(no title)';
	$title = strip_tags($title);
	if (strlen($title) > 40) {
		$title = substr($title, 0, 40) . '...';
	}
	$cat_name = get_catname($row->post_category);
	$author_name = ($authordata) ? $authordata["user_nickname"] : '?';
	$bg = ($row->ID % 2) ? 'bgcolor="#f4f4f4"' : 'bgcolor="#ffffff"';
	$can_edit = ($user_level > 0) && ($user_level >= $authordata[13] || $user_level > 4);
?>
<tr <?php echo $bg ?>>
<td nowrap="nowrap"><?php echo mysql2date($date_format, $row->post_date) ?></td>
<td><a href="e2edit.php?action=edit&post=<?php echo $row->ID ?>"><?php echo $title ?></a></td>
<td><?php echo $cat_name ?></td>
<td><?php echo $author_name ?></td>
<?php if ($can_edit) { ?>
<td><a href="e2edit.php?action=edit&post=<?php echo $row->ID ?>">edit</a></td>
<td><a href="e2edit.php?action=delete&post=<?php echo $row->ID ?>" onclick="return confirm('Delete this post ?')">delete</a></td>
<?php } else { ?>
<td colspan="2">&nbsp;</td>
<?php } ?>
</tr>
<?php } ?>
</table>
<?php
echo $tablebottom;
?>