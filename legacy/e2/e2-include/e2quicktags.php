<?php
/*
 * e2quicktags.php - simple HTML quick-insert buttons for the edit form.
 */
?>
<script type="text/javascript" language="javascript">
function edInsertTag(myField, myValue) {
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	} else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
		myField.focus();
	} else {
		myField.value += myValue;
		myField.focus();
	}
}
</script>
<input type="button" value="b" class="search" onclick="edInsertTag(document.forms[0].content, '[b]&lt;/b&gt;')" />
<input type="button" value="i" class="search" onclick="edInsertTag(document.forms[0].content, '[i]&lt;/i&gt;')" />
<input type="button" value="u" class="search" onclick="edInsertTag(document.forms[0].content, '[u]&lt;/u&gt;')" />
<input type="button" value="a" class="search" onclick="edInsertTag(document.forms[0].content, '&lt;a href=&quot;&quot;&gt;&lt;/a&gt;')" />
<input type="button" value="img" class="search" onclick="edInsertTag(document.forms[0].content, '&lt;img src=&quot;&quot; /&gt;')" />
<br />