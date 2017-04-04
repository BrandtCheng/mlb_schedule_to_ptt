<?php
/**
mlb home/away games counter
http://mlb.mlb.com/gdcross/components/game/mlb/year_2017/month_04/day_02/master_scoreboard.json

 */

date_default_timezone_set('Asia/Taipei');
include('lib_http.php');

echo mlb_home_away_stat();


function bb($color){
	//return "\x15".'['.$color.'m';
	return "\x1b".'['.$color.'m';
}
function mlb_home_away_stat(){

	$opp=array();

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
				continue;
			}
			$games=$games['data']['games']['game'];
			for($i=0,$n=count($games);$i<$n;$i++){
				if(!isset($games[$i])){
					//echo '>'.$m.'/'.$d.' '.$i.":\n";
					continue;
				}
				if(!isset($opp[$games[$i]['home_name_abbrev']])){
					$opp[$games[$i]['home_name_abbrev']]=array();
				}
				if(!isset($opp[$games[$i]['home_name_abbrev']][$games[$i]['away_name_abbrev']])){
					$opp[$games[$i]['home_name_abbrev']][$games[$i]['away_name_abbrev']]=0;
				}
				$opp[$games[$i]['home_name_abbrev']][$games[$i]['away_name_abbrev']]++;
			}
		}
	}
	$s='';

	$ts=array_keys($teams);
	$line1=' \\ A';
	$line2='  \\ ';
	$line3=' H \\';
	$c=array(bb('40'),bb('44'),bb('31'),bb('37'));
	for($i=0;$i<30;$i++){
		$line1.=$c[$i%2].' '.$ts[$i][0];
		$line2.=$c[$i%2].' '.$ts[$i][1];
		$line3.=$c[$i%2].(strlen($ts[$i])>2?' '.$ts[$i][2]:'  ');
	}
	$s=$line1.bb('').$crlf.$line2.bb('').$crlf.$line3.bb('').$crlf;
	for($i=0;$i<30;$i++){
		$s.=bb(1).$c[$i%2+2].sprintf('%3s ',$ts[$i]);
		for($j=0;$j<30;$j++){
			if($ts[$i]==$ts[$j]){
				$s.=bb(30).$c[$j%2].' -'.$c[$i%2+2];
			}
			else if(!isset($opp[$ts[$i]][$ts[$j]])){
				$s.=$c[$j%2].'  ';
			}
			else{
				$s.=$c[$j%2].sprintf('%2d',$opp[$ts[$i]][$ts[$j]]);
			}
		}
		$s.=bb('').$crlf;
	}
	//file_put_contents('c.mlb.txt',$s);
	return $s;
}
