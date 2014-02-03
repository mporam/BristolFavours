<?php if(@$noprice==TRUE){
					print '&nbsp;';
				}else{
					if((double)$rs['pListPrice']!=0.0) print '<div class="detaillistprice">' . str_replace('%s', FormatEuroCurrency($rs['pListPrice']), $xxListPrice) . '</div>';
						print '<div class="detailprice"><strong>' . $xxPrice . ':</strong> <span class="price" id="pricediv' . $Count . '">' . ($rs['pPrice']==0 && @$pricezeromessage != '' ? $pricezeromessage : FormatEuroCurrency(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
					if(@$showtaxinclusive==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
						print '</div>';
					$extracurr = '';
					if($currRate1!=0 && $currSymbol1!='') $extracurr = str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
					if($currRate2!=0 && $currSymbol2!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
					if($currRate3!=0 && $currSymbol3!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3) . '</strong>';
					if($extracurr!='') print '<div class="detailcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
					//print '<hr width="80%" />';
				} ?>