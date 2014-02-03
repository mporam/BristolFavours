<?php
				$longdesc = trim($rs[getlangid('pLongDescription',4)]);
				if(@$usedetailbodyformat==3){
				}elseif($longdesc!='')
					print '<div class="detaildescription">' . displaytabs($longdesc) . '</div>';
				elseif(trim($rs[getlangid('pDescription',2)])!='')
					print '<div class="detaildescription">' . $rs[getlangid('pDescription',2)] . '</div>'; ?>
					
					