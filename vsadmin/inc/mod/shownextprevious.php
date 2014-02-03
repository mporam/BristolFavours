<?php
if($previousid != '' || $nextid != ''){
	print '<tr><td align="center" colspan="4" class="pagenums"><p class="pagenums">&nbsp;<br />';
	writepreviousnextlinks();
	print '</p></td></tr>';
} ?>