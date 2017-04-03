<?php
/**
http://mlb.mlb.com/gdcross/components/game/mlb/year_2017/month_04/day_02/master_scoreboard.json

 */

date_default_timezone_set('Asia/Taipei');
include('lib_http.php');

echo mlb_schedule();

function bb($color){
	return "\x15".'['.$color.'m';
	//return "\x1b".'['.$color.'m';
}
function mlb_schedule(){

	$g=array();

	$crlf="\n";
	$teams=array(
		'NYY'=>bb(';1;33'),
		'BOS'=>bb(';1;33'),
		'BAL'=>bb(';1;33'),
		'TB'=>bb(';1;33'),
		'TOR'=>bb(';1;33'),
		'CWS'=>bb(';1;31'),
		'DET'=>bb(';1;31'),
		'CLE'=>bb(';1;31'),
		'KC'=>bb(';1;31'),
		'MIN'=>bb(';1;31'),
		'LAA'=>bb(';1;37'),
		'SEA'=>bb(';1;37'),
		'OAK'=>bb(';1;37'),
		'HOU'=>bb(';1;37'),
		'TEX'=>bb(';1;37'),
		'NYM'=>bb('1;33;44'),
		'PHI'=>bb('1;33;44'),
		'WSH'=>bb('1;33;44'),
		'MIA'=>bb('1;33;44'),
		'ATL'=>bb('1;33;44'),
		'CHC'=>bb('1;31;44'),
		'STL'=>bb('1;31;44'),
		'PIT'=>bb('1;31;44'),
		'MIL'=>bb('1;31;44'),
		'CIN'=>bb('1;31;44'),
		'SF'=>bb('1;37;44'),
		'LAD'=>bb('1;37;44'),
		'COL'=>bb('1;37;44'),
		'SD'=>bb('1;37;44'),
		'ARI'=>bb('1;37;44'),
	);
	$calmonth=bb(';1;31');
	$calday=bb(';1;31');
	$playtime=bb('1;40;30');
	$at=bb(';').'@';

	$monthes=array(
		'',
		'',
		'',
		'',
		'Apr',
		'May',
		'Jun',
		'Jul',
		'Aug',
		'Sep',
		'Oct',
		'Nov',
	);

	for($m=4;$m<=10;$m++){
		$t=mktime(0,0,0,$m,1,2017);
		$maxday=date('t',$t);
		if($m==10){
			$maxday=1;
		}
		for($d=($m==4?2:1);$d<=$maxday;$d++){
			if(!file_exists('mlb/mlb.2017.'.$m.'.'.$d.'.json')){
				$json_src='http://mlb.mlb.com/gdcross/components/game/mlb/year_2017/month_'.sprintf('%02d',$m).'/day_'.sprintf('%02d',$d).'/master_scoreboard.json';
				$c=http_get($json_src,'http://mlb.mlb.com/');
				if($c['STATUS']['http_code']!=200){
					continue;
				}
				file_put_contents('mlb/mlb.2017.'.$m.'.'.$d.'.json',$c['FILE']);
				$games=json_decode($c['FILE'],TRUE);
			}
			else{
				$c=file_get_contents('mlb/mlb.2017.'.$m.'.'.$d.'.json');
				$games=json_decode($c,TRUE);
			}
			if(!isset($games['data']['games']['game'])){
				break;
			}
			$games=$games['data']['games']['game'];
			$gm=array();
			for($i=0,$n=count($games);$i<$n;$i++){
				if(!isset($games[$i])){
					//echo '>'.$m.'/'.$d.' '.$i.":\n";
					continue;
				}
				$t=$games[$i]['time'];
				list($hh,$ii)=explode(':',$t);
				$hh=intval($hh,10)+12;
				$ii=intval($ii,10);
				$t=sprintf('%02d:%02d',$hh,$ii);
				if(!isset($gm[$t])){
					$gm[$t]=array();
				}
				$gm[$t][]=array(
					'home'=>$games[$i]['home_name_abbrev'],
					'away'=>$games[$i]['away_name_abbrev']
				);
			}
			ksort($gm);
			$g[$m][$d]=$gm;
		}
	}

	$str='';
	for($m=4;$m<=10;$m++){
		$t=mktime(0,0,0,$m,1,2017);
		$maxday=date('t',$t);
		$str.=$crlf.$calmonth.'['.$monthes[$m].']'.bb('').$crlf;
		for($d=1;$d<=$maxday;$d++){
			if(!isset($g[$m][$d])){
				continue;
			}
			$str.=$calday.sprintf('%02d/%02d ',$m,$d);
			$ct=0;
			foreach($g[$m][$d] as $tm => $gs){
				if($ct>0){
					$str.='      ';
				}
				$str.=$playtime.$tm.' ';
				$line=array();
				for($i=0,$n=count($gs);$i<$n;$i++){
					if(!isset($teams[$gs[$i]['away']]) || !isset($teams[$gs[$i]['home']])){
						echo $crlf.$m.'/'.$d.$crlf;
						die('xx');
					}
					$line[]=$teams[$gs[$i]['away']].str_repeat(' ',3-strlen($gs[$i]['away'])).$gs[$i]['away'].$at.$teams[$gs[$i]['home']].$gs[$i]['home'].str_repeat(' ',3-strlen($gs[$i]['home'])).bb('');
				}
				$str.=join('   ',$line).$crlf;
				$ct++;
			}
		}
	}
	return $str;
}
