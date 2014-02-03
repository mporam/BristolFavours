<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$menupoplimit=='') $menupoplimit=9;
$menuid='';
if(@$menustyle=='') $menustyle='';
if(@$_SESSION['clientLoginLevel'] != '') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
$alreadygotadmin = getadminsettings();
if((@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443') && @$forceloginonhttps!=TRUE) $incstoreurl=$storeurl; else $incstoreurl='';
function mwritemenulevel($id,$itlevel){
	global $mAlldata,$numrows,$menupoplimit,$menuprestr,$storeurl,$menucategoriesatroot,$incstoreurl,$menustyle,$jsstr,$menuid,$catalogroot;
	$hassub=FALSE;
	if($itlevel<=$menupoplimit){
		if(! (@$menucategoriesatroot===2 && $id==0)){
			for($mIndex=0;$mIndex < $numrows;$mIndex++){
				if($mAlldata[$mIndex][2]==$id){
					$jsstr.='em['.$mAlldata[$mIndex][0].']='.$mAlldata[$mIndex][2].';';
					if(($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3') && ! $hassub){
						print '<ul id="ecttop'.$menuid.'_'.$id.'" style="list-style:none;margin:0px;border:0px;'.($id!=0?'display:none;position:absolute;':'').'" class="ectmenu'.($menuid+1).($id!=0?' ectsubmenu'.($menuid+1):'').'">';
						$jsstr.='emt['.$menuid.']['.$id.']=false;';
					}
					$hassub=TRUE;
					$mTID = $mAlldata[$mIndex][2];
					if($mTID==0) $mTID = '';
					if($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3'){
						if(! (@$menucategoriesatroot===TRUE && $mAlldata[$mIndex][0]==$catalogroot)){
							print '<li id="ect'.$menuid.'_'.$mAlldata[$mIndex][0].'" class="ectmenu'.($menuid+1).($id!=0?' ectsubmenu'.($menuid+1):'').'" onmouseover="openpop(this,'.($menustyle=='verticalmenu3'?'true':'false').')" onmouseout="closepop(this)" style="list-style:none;'.($id!=0 || $menustyle=='verticalmenu3'?'margin-bottom:-1px;':'display:inline;margin-right:-1px;').'">';
							if(trim($mAlldata[$mIndex][4])!=''){
								print '<a href="'.$incstoreurl.$mAlldata[$mIndex][4].'">'.str_replace('<','&lt;',$mAlldata[$mIndex][1])."</a>\r\n";
							}else{
								if($mAlldata[$mIndex][3]==0)
									print '<a href="'.$incstoreurl.'categories.php?cat='.getcatid($mAlldata[$mIndex][0],$mAlldata[$mIndex][1]).'">'.str_replace('<','&lt;',$mAlldata[$mIndex][1])."</a>\r\n";
								else
									print '<a href="'.$incstoreurl.'products.php?cat='.getcatid($mAlldata[$mIndex][0],$mAlldata[$mIndex][1]).'">'.str_replace('<','&lt;',$mAlldata[$mIndex][1])."</a>\r\n";
							}
							print '</li>';
						}
					}else{
						if(@$menucategoriesatroot===1)
							$menuheadsec = 'mymenu.addMenu(';
						else
							$menuheadsec = 'mymenu.addSubMenu("products' . $mTID . '",';
						if(trim($mAlldata[$mIndex][4]) != ''){
							print $menuheadsec.'"products' . $mAlldata[$mIndex][0] . '","' . @$menuprestr . str_replace('"','\"',$mAlldata[$mIndex][1]) . @$menupoststr . '","' . $incstoreurl . $mAlldata[$mIndex][4] . "\");\n";
						}else{
							if($mAlldata[$mIndex][3]==0)
								print $menuheadsec.'"products' . $mAlldata[$mIndex][0] . '","' . @$menuprestr . str_replace('"','\"',$mAlldata[$mIndex][1]) . @$menupoststr . '","'.$incstoreurl.'categories.php?cat=' . getcatid($mAlldata[$mIndex][0],$mAlldata[$mIndex][1]) . "\");\n";
							else
								print $menuheadsec.'"products' . $mAlldata[$mIndex][0] . '","' . @$menuprestr . str_replace('"','\"',$mAlldata[$mIndex][1]) . @$menupoststr . '","'.$incstoreurl.'products.php?cat=' . getcatid($mAlldata[$mIndex][0],$mAlldata[$mIndex][1]) . "\");\n";
						}
					}
				}
			}
			if(($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3') && $hassub) print '</ul>';
		}
		for($mIndex=0;$mIndex < $numrows;$mIndex++)
			if($mAlldata[$mIndex][2]==$id && $mAlldata[$mIndex][3]==0 && @$menucategoriesatroot!==1) mwritemenulevel($mAlldata[$mIndex][0],$itlevel+1);
	}
}
function mstrdpth($mstr,$dep){
	$mstrd='';
	for($index=2; $index<=$dep; $index++){
		$mstrd.=$mstr.' ';
	}
	return($mstrd);
}
function cssmenulevel($id,$itlevel){
	global $menupoplimit,$menucategoriesatroot,$mAlldata,$jsstr,$numrows,$menuid,$catalogroot,$incstoreurl;
	if($itlevel<=$menupoplimit){
		for($mIndex=0;$mIndex < $numrows;$mIndex++){
			if($mAlldata[$mIndex][2]==$id && ! (@$menucategoriesatroot===TRUE && $mAlldata[$mIndex][0]==$catalogroot)){
				$jsstr.='em['.$mAlldata[$mIndex][0].']='.$mAlldata[$mIndex][2].';';
				if(trim($mAlldata[$mIndex][4])!=''){
					$mlink = $incstoreurl.$mAlldata[$mIndex][4];
				}else{
					if($mAlldata[$mIndex][3]==0)
						$mlink = $incstoreurl.'categories.php?cat='.getcatid($mAlldata[$mIndex][0],$mAlldata[$mIndex][1]);
					else
						$mlink = $incstoreurl.'products.php?cat='.getcatid($mAlldata[$mIndex][0],$mAlldata[$mIndex][1]);
				}
				print '<li class="ectmenu'.($menuid+1).($id!=0?' ectsubmenu'.($menuid+1):'').'" id="ect'.$menuid.'_'.$mAlldata[$mIndex][0].'" onclick="if(!hassubs(this))return true; else return(opencascade(this))" style="'.($mAlldata[$mIndex][2]!=0?'display:none;':'').'margin-bottom:-1px;"><a style="display:block" href="' . $mlink . '">' . ($id!=0?mstrdpth('&raquo;',$itlevel):'') . str_replace('<','&lt;',$mAlldata[$mIndex][1]) . "</a></li>\r\n";
				$jsstr.='emt['.$menuid.']['.$id.']=false;';
				cssmenulevel($mAlldata[$mIndex][0],$itlevel+1);
			}
		}
	}
}
function writesubmenus(){
	global $menucategoriesatroot;
	$menucategoriesatroot=2;
	mwritemenulevel(0,2);
}
function displayectmenu($menstyle){
	global $jsstr,$menupoplimit,$minloglevel,$numrows,$mAlldata,$menuid,$menustyle,$menucategoriesatroot,$catalogroot;
	if(@$menuid==='') $menuid=0; else $menuid++;
	$menustyle=$menstyle;
	$sSQL = 'SELECT sectionID,'.getlangid('sectionName',256).',topSection,rootSection,'.getlangid('sectionurl',2048).' FROM sections WHERE sectionDisabled<=' . $minloglevel . ($menupoplimit<=1?' AND topSection=0':'') . ' ORDER BY sectionOrder' . (@$menustyle=='verticalmenu2'?',topSection':'');
	$result = mysql_query($sSQL) or print(mysql_error());
	$numrows = 0;
	$jsstr='';
	if(mysql_num_rows($result) > 0){
		$theroot=$catalogroot;
		while($rs = mysql_fetch_row($result)){
			$mAlldata[$numrows++]=$rs;
			if($rs[0]==$catalogroot) $theroot=$rs[2];
		}
		if(@$menucategoriesatroot===TRUE && ($menustyle=='verticalmenu2' || $menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3')){
			for($mIndex=0;$mIndex < $numrows;$mIndex++){
				if($mAlldata[$mIndex][2]==$catalogroot) $mAlldata[$mIndex][2]=$theroot;
			}
		}
		if($menustyle=='verticalmenu2'){
			print '<ul class="ectmenu'.($menuid+1).'" style="list-style:none">';
			cssmenulevel(0,1);
			print '</ul>';
		}elseif($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3')
			mwritemenulevel(0,1);
		else
			mwritemenulevel(0,1);
	}
	mysql_free_result($result);
	if($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu2' || $menustyle=='verticalmenu3'){ ?>
<script type="text/javascript" language="javascript">
/* <![CDATA[ */
<?php
		if($menuid==0){
			print 'var curmen=[];var lastmen=[];var em=[];var emt=[];' . "\r\n";
			writemenuscripts();
		}
		print 'emt['.$menuid.']=new Array();curmen['.$menuid."]=0;\r\n";
		print $jsstr . "\r\n";
		print 'addsubsclass('.$menuid.",0)\r\n";
		print '/* ]]> */</script>';
	}
}
function writemenuscripts(){
?>
function closepopdelay(menid){
	var re = new RegExp('ect\\d+_');
	var theid=menid.replace(re,'');
	var mennum = menid.replace('ect','').replace(/_\d+/,'');
	for(var ei in emt[mennum]){
		if(ei!=0&&emt[mennum][ei]==true&&!insubmenu(ei,mennum)){
			document.getElementById('ecttop'+mennum+"_"+ei).style.display='none';
			emt[mennum][ei]=false; // closed
		}
	}
}
function closepop(men){
	var mennum = men.id.replace('ect','').replace(/_\d+/,'');
	lastmen[mennum]=curmen[mennum];
	curmen[mennum]=0;
	setTimeout("closepopdelay('"+men.id+"')",1000);
}
function getPos(el){
	for (var lx=0,ly=0; el!=null; lx+=el.offsetLeft,ly+=el.offsetTop, el=el.offsetParent){
	};
	return{x:lx,y:ly};
}
function openpop(men,ispopout){
	var re = new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	var mennum = men.id.replace('ect','').replace(/_\d+/,'');
	curmen[mennum]=theid;
	if(lastmen[mennum]!=0)
		closepopdelay('ect'+mennum+'_'+lastmen[mennum]);
	if(mentop=document.getElementById('ecttop'+mennum+'_'+theid)){
		var px = getPos(men);
		if(em[theid]==0&&!ispopout){
			mentop.style.left=px.x+'px';
			mentop.style.top=(px.y+men.offsetHeight-1)+'px';
			mentop.style.display='';
		}else{
			mentop.style.left=(px.x+men.offsetWidth-1)+'px';
			mentop.style.top=px.y+'px';
			mentop.style.display='';
		}
		emt[mennum][theid]=true; // open
	}
}
function hassubs(men){
	var re = new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	for(var ei in em){
		if(em[ei]==theid)
			return(true);
	}
	return(false);
}
function closecascade(men){
	var re = new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	var mennum = men.id.replace('ect','').replace(/_\d+/,'');
	curmen[mennum]=0;
	for(var ei in emt[mennum]){
		if(ei!=0&&emt[mennum][ei]==true&&!insubmenu(ei,mennum)){
			for(var ei2 in em){
				if(em[ei2]==ei){
					document.getElementById('ect'+mennum+"_"+ei2).style.display='none';
				}
			}
		}
	}
	emt[mennum][theid]=false; // closed
	return(false);
}
function opencascade(men){
	var re = new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	var mennum = men.id.replace('ect','').replace(/_\d+/,'');
	if(emt[mennum][theid]==true) return(closecascade(men));
	var mennum = men.id.replace('ect','').replace(/_\d+/,'');
	curmen[mennum]=theid;
	for(var ei in em){
		if(em[ei]==theid){
			document.getElementById('ect'+mennum+'_'+ei).style.display='';
			emt[mennum][theid]=true; // open
		}
	}
	return(false);
}
function writedbg(txt){
	if(document.getElementById('debugdiv')) document.getElementById('debugdiv').innerHTML+=txt+"<br />";
}
function insubmenu(mei,mid){
	if(curmen[mid]==0)return(false);
	curm=curmen[mid];
	maxloops=0;
	while(curm!=0){
		if(mei==curm)return(true);
		curm=em[curm];
		if(maxloops++>10) break;
	}
	return(false);
}
function addsubsclass(mennum,menid){
	for(var ei in em){
		if(typeof(emt[mennum][ei])=='boolean'){
			men = document.getElementById('ect'+mennum+'_'+ei);
			if(men.className.indexOf('ectmenuhassub')==-1)men.className+=' ectmenuhassub'+(mennum+1);
		}
	}
}
<?php
} // writemenuscripts
if(@$menuid==''){
	displayectmenu($menustyle);
}
?>