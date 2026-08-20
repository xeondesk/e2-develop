<?php
/*
 * e2edit.form.php - the "write / edit" form, shared by e2edit.php.
 *
 * Branching on $action:
 *   - "post"        : brand new post
 *   - "edit"        : edit an existing post ($post holds the post ID)
 *   - "editcomment" : edit an existing comment ($commentdata set)
 */
echo $tabletop;
?>

<form name="post" action="e2edit.php" method="POST" accept-charset="iso-8859-1">
<input type="hidden" name="user_ID" value="<?php echo $user_ID ?>" />

<?php if ($action == "post") { ?>
<input type="hidden" name="action" value="post" />

<p>
<label for="post_title"><b>Title:</b></label><br />
<input type="text" name="post_title" id="post_title" size="40" tabindex="1" style="width: 95%;" />
</p>

<p>
<label for="post_category"><b>Category:</b></label><br />
<?php dropdown_categories(); ?>
</p>

<p>
<label for="content"><b>Post:</b></label><br />
<textarea rows="10" cols="45" style="width: 95%" name="content" id="content" tabindex="2" class="postform" wrap="virtual"></textarea>
</p>

<?php } elseif ($action == "edit") { ?>
<input type="hidden" name="action" value="editpost" />
<input type="hidden" name="post_ID" value="<?php echo $post ?>" />

<p>
<label for="post_title"><b>Title:</b></label><br />
<input type="text" name="post_title" id="post_title" size="40" tabindex="1" style="width: 95%;" value="<?php echo $edited_post_title ?>" />
</p>

<p>
<label for="post_category"><b>Category:</b></label><br />
<?php dropdown_categories(); ?>
</p>

<p>
<label for="content"><b>Post:</b></label><br />
<textarea rows="10" cols="45" style="width: 95%" name="content" id="content" tabindex="2" class="postform" wrap="virtual"><?php echo $content ?></textarea>
</p>

<?php } elseif ($action == "editcomment") { ?>
<input type="hidden" name="action" value="editedcomment" />
<input type="hidden" name="comment_ID" value="<?php echo $commentdata["comment_ID"] ?>" />
<input type="hidden" name="comment_post_ID" value="<?php echo $commentdata["comment_post_ID"] ?>" />

<p>
<b>Name:</b><br />
<input type="text" name="newcomment_author" size="40" tabindex="1" value="<?php echo $commentdata["comment_author"] ?>" />
</p>
<p>
<b>E-mail:</b><br />
<input type="text" name="newcomment_author_email" size="40" tabindex="2" value="<?php echo $commentdata["comment_author_email"] ?>" />
</p>
<p>
<b>URL:</b><br />
<input type="text" name="newcomment_author_url" size="40" tabindex="3" value="<?php echo $commentdata["comment_author_url"] ?>" />
</p>
<p>
<b>Comment:</b><br />
<textarea rows="8" cols="45" style="width: 95%" name="content" tabindex="4" class="postform" wrap="virtual"><?php echo $content ?></textarea>
</p>

<?php } ?>

<?php if ($use_quicktags) { ?>
<p>
<script type="text/javascript" language="javascript">
function edInsertTag(myField, myValue) {
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	} else {
		myField.value += myValue;
	}
}
</script>
<input type="button" value="b" class="search" onclick="edInsertTag(document.forms[0].content, '[b]&lt;/b&gt;')" />
<input type="button" value="i" class="search" onclick="edInsertTag(document.forms[0].content, '[i]&lt;/i&gt;')" />
<input type="button" value="u" class="search" onclick="edInsertTag(document.forms[0].content, '[u]&lt;/u&gt;')" />
<input type="button" value="a" class="search" onclick="edInsertTag(document.forms[0].content, '&lt;a href=&quot;&quot;&gt;&lt;/a&gt;')" />
<input type="button" value="img" class="search" onclick="edInsertTag(document.forms[0].content, '&lt;img src=&quot;&quot; /&gt;')" />
<br />
</p>
<?php } ?>

<p>
<input type="checkbox" name="post_autobr" value="1" checked="checked" tabindex="6" class="checkbox" id="autobr" /><label for="autobr"> Auto-BR (line-breaks become &lt;br /> tags)</label>
</p>

<?php if ($use_pingback) { ?>
<p>
<input type="checkbox" class="checkbox" name="post_pingback" value="1" checked="checked" tabindex="7" id="pingback" /><label for="pingback"> Send PingBacks</label>
</p>
<?php } ?>

<?php if ($use_trackback) { ?>
<p>
<label for="trackback"><b>TrackBack</b> URLs (comma separated):</label><br />
<input type="text" name="trackback_url" style="width: 95%" id="trackback" tabindex="8" />
</p>
<?php } ?>

<?php if ($user_level > 4) { ?>
<p>
<input type="checkbox" name="edit_date" value="1" tabindex="9" class="checkbox" id="editdate" /><label for="editdate"> Edit the date</label><br />
<input type="text" name="aa" value="<?php echo date('Y') ?>" size="4" />-<input type="text" name="mm" value="<?php echo date('m') ?>" size="2" />-<input type="text" name="jj" value="<?php echo date('d') ?>" size="2" /> <input type="text" name="hh" value="<?php echo date('H') ?>" size="2" />:<input type="text" name="mn" value="<?php echo date('i') ?>" size="2" />:<input type="text" name="ss" value="<?php echo date('s') ?>" size="2" />
</p>
<?php } ?>

<p>
<input type="submit" name="submit" value="<?php echo ($action == 'edit') ? 'Update post !' : (($action == 'editcomment') ? 'Update comment !' : 'Blog this !'); ?>" class="search" tabindex="10" />
</p>
</form>

<?php echo $tablebottom; ?>