<?php
/*
data source:
http://newyork.yankees.mlb.com/ticketing-client/json/Game.tiksrv?team_id=147&site_section=SCHEDULE&sport_id=1&start_date=20170327&events=10
download and save as "mlb.[team_id].2017.json" with this script file
	ex:yankees = mlb.NYY.2017.json
 */

date_default_timezone_set('Asia/Taipei');

function bb($color){
	return "\x15".'['.$color.'m'; // post to bbs
	//return "\x1b".'['.$color.'m'; // show at screen
}

echo mlb_schedule();

function mlb_schedule(){
	// CRLF, "\n" for saved to file, "\r": for posted by telnet fsocket
	$crlf="\n";
	// calendar table's border color
	$border=36;
	// calendar table's month title background color
	$monthbg=46;
	// calendar table's month title text color
	$monthtext=33;
	// calendar table's week name color
	$calweek=33;
	// calendar table's day number color at every month
	$calday=32;
	// when the game at home, the opp. team name color
	$athome=35;
	// when the game at away, the opp. team name color
	$ataway=37;
	// game start time at ET timezone
	$playtime=30;
	// [team_id]
	$team_id='TB';

	$games=json_decode(file_get_contents('mlb.'.$team_id.'.2017.json'),TRUE);
	$games=$games['events']['game'];
	$num_games=count($games);

	$g=array();
	$opp=array();

	for($i=0;$i<$num_games;$i++){
		if($games[$i]['gt_name_short']=='Spring'){
			continue;
		}
		list($y,$m,$d)=explode('-',$games[$i]['game_date']);
		$m=intval($m,10);
		$d=intval($d,10);
		
		$time=$games[$i]['game_time_et'];
		if(preg_match('/^\d+\s(\d+):(\d+):\d+\s([APM]+)$/',$time,$matches)){
			$hh=intval($matches[1],10);
			$ii=intval($matches[2],10);
			if($matches[3]=='PM'){
				$hh+=12;
			}
			$time=sprintf('%02d:%02d',$hh,$ii);
		}
		if(!isset($g[$m])) $g[$m]=array();

		$g[$m][$d]=array(
			'home'=>$games[$i]['home_name_abbrev'],
			'away'=>$games[$i]['away_name_abbrev'],
			'time'=>$time,
		);

		if($games[$i]['home_name_abbrev']==$team_id){
			if(!isset($opp[$games[$i]['away_name_abbrev']])){
				$opp[$games[$i]['away_name_abbrev']]=array(
					'times'=>0,
					'home'=>0,
					'away'=>0
				);
			}
			$opp[$games[$i]['away_name_abbrev']]['times']++;
			$opp[$games[$i]['away_name_abbrev']]['home']++;
		}
		else{
			if(!isset($opp[$games[$i]['home_name_abbrev']])){
				$opp[$games[$i]['home_name_abbrev']]=array(
					'times'=>0,
					'home'=>0,
					'away'=>0
				);
			}
			$opp[$games[$i]['home_name_abbrev']]['times']++;
			$opp[$games[$i]['home_name_abbrev']]['away']++;
		}
	}
	/**
	foreach($opp as $team => $v){
		echo $team.",".$v['home'].",".$v['away'].$crlf;
	}
	**/

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
	$str='';
	for($m=4;$m<=10;$m++){
		if($m==4){
			$str=bb('1;'.$border).'╭────┬────┬────┬────┬────┬'.bb($monthbg.';'.$monthtext).'      Apr 2017    '.bb($border.';40').'╮'.bb('').$crlf;
		}
		else{
			$str.=bb('1;'.$border).'├────┼────┼────┼────┼────┼'.bb($monthbg.';'.$monthtext).'      '.$monthes[$m].' 2017    '.bb($border.';40').'┤'.bb('').$crlf;
		}
		$timestamp_month=mktime(0,0,0,$m,1,2017);
		$w_day1=date('w',$timestamp_month);
		$str.=bb('1;'.$border).'│'.bb($calweek).'Sun.    '.bb($border).'│'.bb($calweek).'Mon.    '.bb($border).'│'.bb($calweek).'Tue.    '.bb($border).'│'.bb($calweek).'Wed.    '.bb($border).'│'.bb($calweek).'Thu.    '.bb($border).'│'.bb($calweek).'Fri.    '.bb($border).'│'.bb($calweek).'Sat.    '.bb($border).'│'.bb('').$crlf;
		$str.=bb('1;'.$border).'├────┼────┼────┼────┼────┼────┼────┤'.bb('').$crlf;
		$line1='';
		$line2='';
		$line3='';

		$line1.=bb('1;'.$border).'│';
		$line2.=bb('1;'.$border).'│';
		$line3.=bb('1;'.$border).'│';
		if($w_day1){
			for($tmp=0;$tmp<$w_day1;$tmp++){
				$line1.='        │';
				$line2.='        │';
				$line3.='        │';
			}
		}
		$num_days=date('t',$timestamp_month);
		for($d=1;$d<=$num_days;$d++){
			$timestamp_day=mktime(0,0,0,$m,$d,2017);
			$w=date('w',$timestamp_day);
			if(!isset($g[$m][$d])){
				$line1.=bb($calday).($d>9?$d:$d.' ').'      '.bb($border).'│';
				$line2.='        │';
				$line3.='        │';
			}
			else{
				$line1.=bb($calday).($d>9?$d:$d.' ').'  ';
				$at_home=$g[$m][$d]['home']==$team_id?'':'@';
				if($g[$m][$d]['home']==$team_id){
					$line1.=bb($athome).sprintf('%4s',$g[$m][$d]['away']);
				}
				else{
					$line1.=bb($ataway).sprintf('%4s','@'.$g[$m][$d]['home']);
				}
				$line1.=bb($border).'│';
				$line2.='        │';
				$line3.=bb($playtime).'   '.$g[$m][$d]['time'].bb($border).'│';
			}
			if($w>=6){
				$str.=$line1.bb('').$crlf;
				$str.=$line2.bb('').$crlf;
				$str.=$line3.bb('').$crlf;
				if($d<$num_days){
					$str.=bb('1;'.$border).'├────┼────┼────┼────┼────┼────┼────┤'.bb('').$crlf;
					$line1=bb('1;'.$border).'│';
					$line2=bb('1;'.$border).'│';
					$line3=bb('1;'.$border).'│';
				}
			}
		}
		if($w<6){
			for($tmp=0,$tmp2=6-$w;$tmp<$tmp2;$tmp++){
				$line1.='        │';
				$line2.='        │';
				$line3.='        │';
			}
			$str.=$line1.bb('').$crlf;
			$str.=$line2.bb('').$crlf;
			$str.=$line3.bb('').$crlf;
		}
	}
	$str.=bb('1;'.$border).'╰────┴────┴────┴────┴────┴────┴────╯'.bb('');

	return $str;
}


