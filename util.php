<?php

/*
# -*- coding: utf-8 -*-
# @Author: h1xa
# @Date:   2020-09-22 15:40:11
# @Last Modified by:   h1xa
# @Last Modified time: 2020-09-25 20:16:52
# @email: h1xa@ctfer.com
# @link: https://ctfer.com

*/


//返回距离指定日期的时间
function formate_date($date){
	$str1 = $date;
	$str2 = str_replace("T", " ", $str1);
	$str3 = str_replace("Z", "", $str2);
	$startdate=date_create($str3);
	$enddate=date_create();
	$diff=date_diff($startdate,$enddate);

	if($diff->d>0){
		return $diff->d."天前";
	}elseif (($diff->h)-8>0) {
		return intval(($diff->h)-8)."小时前";
	}elseif ($diff->i>0) {
		return $diff->i."分钟前";
	}else{
		return "刚刚";
	}
}

/**
 * 二维数组根据某个字段排序
 */
function arraySort($array, $keys, $sort = SORT_DESC) {
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}


function geturl($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
}

class challenge{
	var $id,$name,$value,$solves;
	function __construct($id,$name,$value,$solves){
		$this->id = $id;
		$this->name = $name;
		$this->value =$value;
		$this->solves = $solves;
	}
}


class challenges
{
	var $challs = array();
	public function pullChall($chall){
		array_push($this->challs, $chall);
	}
	public function getallchall(){
		return $this->challs;
	}
}

class user
{
	var $id,$name,$value,$rank;
	var $challs = array();
	function __construct($id,$name,$value){
		$this->id = $id;
		$this->name = $name;
		$this->value =$value;
	}
	function setSolve($chall){
		array_push($this->challs, $chall);
	}
	function getAllSolve(){
		return $this->challs;
	}
	function addValue($value){
		$this->value = intval($this->value) + intval($value);
	}

}

class users
{
	var $users = array();
	public function pullUser($user){
		$flag = 1;
		foreach ($this->users as $u) {
			if($u->id==$user->id){
				$flag=0;
			}
		}
		if($flag){
			array_push($this->users, $user);
		}
		
	}
	public function getUserById($id){
		foreach ($this->users as $u) {
			if($u->id==$id){
				return $u;
			}
		}
		return 0;
	}
	public function getAllUsers(){

		//删除chall变量
		foreach ($this->users as $u) {
			$u->challs = 0;
		}
		return $this->sortUsers();
	}
	public function getTopUsers($length=5){
		$retUser = array();
		$temp = $this->sortUsers();
		for ($i=0; $i < $length; $i++) { 
			if(isset($temp[$i])){
				array_push($retUser, $temp[$i]);
			}
			
		}
		//这里反序是为迎合腾讯的bug
		return array_reverse($retUser);
	}
	public function sortUsers(){
		$tempUser = array();
		$retUser = array();
		foreach ($this->users as $u) {
			array_push($tempUser, array("id"=>$u->id,"value"=>$u->value));
		}
		$tempUser=arraySort($tempUser,"value");
		for ($i=0; $i < count($tempUser); $i++) { 
			$rUser = $this->getUserById($tempUser[$i]['id']);
			$rUser->rank=$i+1;
			array_push($retUser, $rUser);
		}
		return $retUser;
	}
}
