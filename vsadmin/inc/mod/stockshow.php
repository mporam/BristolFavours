<?php if($useStockManagement && @$showinstock==TRUE){ if((int)$rs["pStockByOpts"]==0) print '<div class="prodinstock detailinstock"><strong>' . $xxInStoc . ':</strong> ' . $rs["pInStock"] . '</div>'; } ?>