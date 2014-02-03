<?php
	if(@$manufacturerfield!='' && ! is_null($rs['mfName'])) print '<div class="prodmanufacturer detailmanufacturer"><strong>' . $manufacturerfield . ':</strong> ' . $rs['mfName'] . '</div>';
?>