<?php

/*
# -*- coding: utf-8 -*-
# @Author: h1xa
# @Date:   2020-09-22 15:29:46
# @Last Modified by:   h1xa
# @Last Modified time: 2020-09-25 19:45:41
# @email: h1xa@ctfer.com
# @link: https://ctfer.com

*/
include("util.php");
define('CONFIG_START', true); //全局开始标志
define("API__CHALL__", "https://ctf.show/api/v1/challenges"); //获取所有题目接口
define("API__CHALL__SOLVES__", "https://ctf.show/api/v1/challenges/"); //指定题目获取solves接口
define("CONFIG_INIT_SCORE__", 1000); //初始分数
define("CONFIG_MIN_SCORE__", 0); //最低分数
define('CONFIG_DECAY__', 9); //下降率
define('CONFIG_FIRST_BUFF', 1.05); //一血buff
define('CONFIG_SECOND_BUFF', 1.03); //二血buff
define('CONFIG_THIRD_BUFF', 1.01); //三血buff
define('CONFIG_NORMAL_BUFF', 1.00); //正常buff

define('CONFIG_END_DATE', '2020-9-27 18:00:00'); //比赛结束时间
header("Access-Control-Allow-Origin: *"); //支持跨站
header("Access-Control-Allow-Methods:POST,GET");
header("Access-Control-Allow-Headers:x-requested-with,content-type");
header("Content-type:text/json;charset=utf-8");

//比赛题目数组，题目量较小可以直接此处初始化，减小系统开销
$ybb_chall = array('473'=>'web1_此夜圆','474'=>'web3_莫负婵娟','481'=>'附加misc_问青天','482'=>'web2_故人心','483'=>'crypto1_中秋月','484'=>'crypto2_月自圆','485'=>'crypto3_多少离怀','486'=>'misc1_共婵娟','492'=>'pwn_天涯共此时','493'=>'re1_西北望乡','494'=>'re2_归心','495'=>'re3_若无月','496'=>'misc2_洗寰瀛','497'=>'misc3_人生由命');
$challs = new challenges();
//error_reporting(0);
$type=$_GET['t'];
switch ($type) {
	//获取题目列表信息
    case 'chall':
    	if(!CONFIG_START){
    		break;
    	}
        foreach ($ybb_chall as $key=>$value) {
                $solves = count(geturl(API__CHALL__SOLVES__.$key."/solves")['data']);
                if($solves==0){
                    $v=1000;
                }else{
                   $v=intval((CONFIG_INIT_SCORE__- CONFIG_MIN_SCORE__)*(CONFIG_DECAY__+1)/($solves+CONFIG_DECAY__)+CONFIG_MIN_SCORE__); 
                }   
                $challs->pullChall(new challenge($key,$value,$v,$solves));
        }
        echo json_encode($challs->getallchall());
        break;
    case 'challScore':
    	$scores = array();
    	if(!CONFIG_START){
    		break;
    	}
    	foreach ($ybb_chall as $key=>$value) {
                $solves = geturl(API__CHALL__SOLVES__.$key."/solves")['data'];
                if(count($solves)==0){
                    $v=1000;
                }else{
                   $v=intval((CONFIG_INIT_SCORE__- CONFIG_MIN_SCORE__)*(CONFIG_DECAY__+1)/(count($solves)+CONFIG_DECAY__)+CONFIG_MIN_SCORE__); 
                } 
                array_push($scores, array("name"=>substr($value, 0,strpos($value, "_")),"value"=>$v));
        }
        echo json_encode($scores);
    	break;
    //前5名选手得分
    case 'getTopUser':
    	if(!CONFIG_START){
    		break;
    	}
    	$users = new Users();
    	foreach ($ybb_chall as $key=>$value) {
                $solves = geturl(API__CHALL__SOLVES__.$key."/solves")['data'];
                if(count($solves)==0){
                    $v=1000;
                }else{
                   $v=intval((CONFIG_INIT_SCORE__- CONFIG_MIN_SCORE__)*(CONFIG_DECAY__+1)/(count($solves)+CONFIG_DECAY__)+CONFIG_MIN_SCORE__); 
                } 
                for ($i=0; $i < count($solves); $i++) { 
                	switch ($i) {
                		case 0:
                			//print_r($solves);
                			//判断是否已经存在此选手
                			$user=$users->getUserById($solves[$i]['account_id']);
                			//如果存在，则更新其分数
                			if($user){
                				$user->addValue($v*CONFIG_FIRST_BUFF);
                			}else{
                				//不存在，新建一个选手对象，初始化其分数
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_FIRST_BUFF)));
                			}
                			break;
                		case 1:
                			$user=$users->getUserById($solves[$i]['account_id']);
                			if($user){
                				$user->addValue($v*CONFIG_SECOND_BUFF);
                			}else{
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_SECOND_BUFF)));
                			}
                			break;
                		case 2:
                			$user=$users->getUserById($solves[$i]['account_id']);
                			if($user){
                				$user->addValue($v*CONFIG_THIRD_BUFF);
                			}else{
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_THIRD_BUFF)));
                			}
                			break;
                		default:
                			$user=$users->getUserById($solves[$i]['account_id']);
                			if($user){
                				$user->addValue($v*CONFIG_NORMAL_BUFF);
                			}else{
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_NORMAL_BUFF)));
                			}
                			break;
                	}
                }

        }
        echo json_encode($users->getTopUsers());
    	break;
    // 选手积分列表
    case 'list':
    	if(!CONFIG_START){
    		break;
    	}
    	$users = new Users();
    	foreach ($ybb_chall as $key=>$value) {
                $solves = geturl(API__CHALL__SOLVES__.$key."/solves")['data'];
                if(count($solves)==0){
                    $v=1000;
                }else{
                   $v=intval((CONFIG_INIT_SCORE__- CONFIG_MIN_SCORE__)*(CONFIG_DECAY__+1)/(count($solves)+CONFIG_DECAY__)+CONFIG_MIN_SCORE__); 
                } 
                for ($i=0; $i < count($solves); $i++) { 
                	switch ($i) {
                		case 0:
                			//print_r($solves);
                			//判断是否已经存在此选手
                			$user=$users->getUserById($solves[$i]['account_id']);
                			//如果存在，则更新其分数
                			if($user){
                				$user->addValue($v*CONFIG_FIRST_BUFF);
                			}else{
                				//不存在，新建一个选手对象，初始化其分数
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_FIRST_BUFF)));
                			}
                			break;
                		case 1:
                			$user=$users->getUserById($solves[$i]['account_id']);
                			if($user){
                				$user->addValue($v*CONFIG_SECOND_BUFF);
                			}else{
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_SECOND_BUFF)));
                			}
                			break;
                		case 2:
                			$user=$users->getUserById($solves[$i]['account_id']);
                			if($user){
                				$user->addValue($v*CONFIG_THIRD_BUFF);
                			}else{
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_THIRD_BUFF)));
                			}
                			break;
                		default:
                			$user=$users->getUserById($solves[$i]['account_id']);
                			if($user){
                				$user->addValue($v*CONFIG_NORMAL_BUFF);
                			}else{
                				$users->pullUser(new user($solves[$i]['account_id'],$solves[$i]['name'],intval($v*CONFIG_NORMAL_BUFF)));
                			}
                			break;
                	}
                }

        }
        echo json_encode($users->getAllUsers());
    	break;
     // 比赛剩余分钟数
     case 'getBuffTime':
     	if(!CONFIG_START){
    		break;
    	}
     	$startdate=date_create();
		$enddate=date_create(CONFIG_END_DATE,timezone_open("Asia/Shanghai"));
     	$diff=date_diff($startdate,$enddate);
     	$minutes = $diff->d * 24 * 60 + $diff->h * 60 + $diff->i;
     	if($minutes<2880 && $minutes>0){
     		echo json_encode(array(array("value"=>"".$minutes)));
     	}else{
     		echo json_encode(array(array("value"=>"未开赛")));
     	}
     	break;
    // 剩余时间比例
    case 'getCountDown':
    	if(!CONFIG_START){
    		break;
    	}
     	$startdate=date_create();
		$enddate=date_create(CONFIG_END_DATE,timezone_open("Asia/Shanghai"));
     	$diff=date_diff($startdate,$enddate);
     	$minutes = $diff->d * 24 * 60 + $diff->h * 60 + $diff->i;
     	$rate = sprintf("%.2f",$minutes/2880);
     	if($minutes<2880 && $minutes>0){
     		echo json_encode(array(array("value"=>$rate*100)));
     	}else{
     		echo json_encode(array(array("value"=>0)));
     	}
    	break;
    // 全部题目被AK比例
    case 'ak':
    	if(!CONFIG_START){
    		break;
    	}
    	$users = array();
    	$chall_number = 0;
    	$chall_check_number = 0;
        foreach ($ybb_chall as $key=>$value) {
            $solves = count(geturl(API__CHALL__SOLVES__.$key."/solves")['data']);
            if($solves>0){
            	$chall_check_number +=1;
            }
            $chall_number +=1;
        }
        $rate = sprintf("%.2f",$chall_check_number/$chall_number);
        echo json_encode(array(array("value"=>$rate*100)));
    	break;
    // 所有题目的前三血信息
    case 'threeblood':
    	if(!CONFIG_START){
    		break;
    	}
        $ret = array();
        foreach ($ybb_chall as $key =>$value) {
                $first = geturl(API__CHALL__SOLVES__.$key."/solves")['data'][0];
                $second = geturl(API__CHALL__SOLVES__.$key."/solves")['data'][1];
                $thrid = geturl(API__CHALL__SOLVES__.$key."/solves")['data'][2];
                if(isset($first)){
                	array_push($ret, array("name"=>$first['name'],"date"=>formate_date($first['date']),"blood"=>"一血","chall"=>$value));
                }
                if(isset($second)){
                	array_push($ret, array("name"=>$second['name'],"date"=>formate_date($second['date']),"blood"=>"二血","chall"=>$value));
                }
                if(isset($thrid)){
                	array_push($ret, array("name"=>$thrid['name'],"date"=>formate_date($thrid['date']),"blood"=>"三血","chall"=>$value));
                }
        }
        echo json_encode($ret);
    	break;
    default:
        # code...
        break;
}
