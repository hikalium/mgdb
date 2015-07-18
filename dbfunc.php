<?php
require_once("dbquery.php");
// for DEBUG
mysqli_report(MYSQLI_REPORT_ERROR);

//
// Database management
//
function connectDB()
{
	$db = new mysqli('localhost', DB_USER, DB_PASS, DB_NAME);
	if (mysqli_connect_error()) {
		// DB connect error
		die(mysqli_connect_error());
	}
	// 文字化け防止
	$db->set_charset("utf8");
	fixDB($db);
	return $db;
}

function rebuildDB($db)
{
	// 削除
	$stmt = $db->query("drop table if exists AtomElement");
	$stmt = $db->query("drop table if exists RelationElement");
	// 再構築
	fixDB($db);
}

function fixDB($db)
{
	// 足りないテーブルを作成する
	$stmt = $db->query(QUERY_CREATE_TABLE_AtomElement);
	$stmt = $db->query(QUERY_CREATE_TABLE_RelationElement);
}

//
// DB request functions
//

//
// Add / Update / Remove
//
function db_addAtomElement($db, $contents)
{
	// $retv[0] = $stmt->errno (0 if success)
	// $retv[1] = $eid (assigned) or $stmt->error if some error occurred.
	
	$retv = array();
	$stmt = $db->prepare(QUERY_ADD_AtomElement);
	$eid = uuidv4();
	$stmt->bind_param(QUERY_ADD_AtomElement_TYPES, $eid, $contents);
	$stmt->execute();
	//
	$stmt->store_result();
	$retv[0] = $stmt->errno;
	if($stmt->errno == 0){
		$retv[1] = $eid;
	} else{
		$retv[1] = $stmt->error;
	}
	$stmt->close();
	return $retv;
}

function db_getAllAtomElement($db)
{
	// [0, [$eid, $contents, $cDate, $mDate], ...]
	// or
	// [$stmt->errno, $stmt->error]
	$retv = Array();
	$stmt = $db->prepare(QUERY_SELECT_ALL_AtomElement);
	$stmt->execute();
	//
	$stmt->store_result();
	$retv[] = $stmt->errno;
	if($stmt->errno == 0){
		$stmt->bind_result($raweid, $contents, $cDate, $mDate);
		while($stmt->fetch()){
			$eid = getFormedUUIDString($raweid);
			$retv[] = Array($eid, $contents, $cDate, $mDate);
		}
	} else{
		$retv[] = $stmt->error;
	}
	$stmt->close();
	return $retv;
}

/*
function db_removeUser($db, $uid)
{
	$retv = null;
	$stmt = $db->prepare(QUERY_DELETE_User_BY_uid);
	$stmt->bind_param(QUERY_DELETE_User_BY_uid_TYPES, $uid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
	} else{
		if($db->affected_rows == 0){
			$retv = "存在しないユーザーを削除しようとしました。";
		}
	}
	$stmt->close();
	return $retv;
}

function db_addClass($db, $className)
{
	// retv is cid or null.
	$stmt = $db->prepare(QUERY_ADD_Class);
	$stmt->bind_param(QUERY_ADD_Class_TYPES, $className);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		return null;
	}
	$stmt->close();
	//
	$stmt = $db->prepare(QUERY_SELECT_Class_BY_classname);
	$stmt->bind_param(QUERY_SELECT_Class_BY_classname_TYPES, $className);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		return null;
	}
	$stmt->bind_result($cid);
	$stmt->fetch();
	$stmt->close();
	return getFormedUUIDString($cid);
}

function db_removeClass($db, $cid)
{
	// retv: error message (null when success)
	$stmt = $db->prepare(QUERY_DELETE_Class_BY_cid);
	$stmt->bind_param(QUERY_DELETE_Class_BY_cid_TYPES, $cid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
		$stmt->close();
		return $retv;
	}
	$stmt->close();
	//
	$stmt = $db->prepare(QUERY_DELETE_ClassMember_BY_cid);
	$stmt->bind_param(QUERY_DELETE_ClassMember_BY_cid_TYPES, $cid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
		$stmt->close();
		return $retv;
	}
	$stmt->close();
	return false;
}

function db_addQuestion($db, $qDescription, $qHTML)
{
	// retv is null when succeeded.
	$retv = null;
	$stmt = $db->prepare(QUERY_ADD_Question);
	$stmt->bind_param(QUERY_ADD_Question_TYPES, $qDescription, $qHTML);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
	}
	$stmt->close();
	return $retv;
}

function db_updateQuestion($db, $qid, $qDescription, $qHTML)
{
	// retv is null when succeeded.
	$retv = null;
	$stmt = $db->prepare(QUERY_UPDATE_Question);
	$stmt->bind_param(QUERY_UPDATE_Question_TYPES, $qDescription, $qHTML, $qid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
	}
	$stmt->close();
}

function db_removeQuestion($db, $qid)
{
	// retv is null when succeeded.
	$retv = null;
	$stmt = $db->prepare(QUERY_DELETE_Question_BY_qid);
	$stmt->bind_param(QUERY_DELETE_Question_BY_qid_TYPES, $qid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
	} else if($db->affected_rows == 0){
		$retv = "存在しない問題を削除しようとしました。";
	}
	$stmt->close();
	return $retv;
}

function db_addAnswerTag($db, $qid, $uid, $key, $value)
{
	$stmt = $db->prepare(QUERY_ADD_Answer);
	$stmt->bind_param(QUERY_ADD_Answer_TYPES, $qid, $uid, $key, $value);
	$stmt->execute();
	//
	if($stmt->errno != 0){
		echo($stmt->error);
	}
	$stmt->close();
}

function db_addClassMember($db, $uid, $cid)
{
	// retv is null when succeeded.
	$retv = null;
	//
	$stmt = $db->prepare(QUERY_ADD_ClassMember);
	$stmt->bind_param(QUERY_ADD_ClassMember_TYPES, $uid, $cid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$retv = $stmt->error;
	}
	$stmt->close();
	return $retv;
}

//
//	Get data
//

function db_check_qid_isAnsweredBy_uid($db, $qid, $uid)
{
	$retv = false;

	$stmt = $db->prepare(QUERY_SELECT_Answer_BY_qid_AND_uid);
	$stmt->bind_param(QUERY_SELECT_Answer_BY_qid_AND_uid_TYPES, $qid, $uid);
	$stmt->execute();
	//
	$stmt->store_result();
	
	$retv = ($stmt->num_rows != 0);
	
	$stmt->close();
	
	return $retv;
}

function db_getQuestionDataBy_qid($db, $qid)
{
	// [qid, qHTML, qDescription]
	$retv = null;

	$stmt = $db->prepare(QUERY_SELECT_Question_data_BY_qid);
	$stmt->bind_param(QUERY_SELECT_Question_data_BY_qid_TYPES, $qid);
	$stmt->execute();
	//
	$stmt->store_result();
	
	$stmt->bind_result($qHTML, $qDescription);
	if($stmt->errno == 0 && $stmt->num_rows === 1){
		$stmt->fetch();
		$retv = Array($qid, $qHTML, $qDescription);
	}
	
	$stmt->close();
	
	return $retv;
}

function db_getQuestionDataList_NotAnswerdByUser($db, $uid)
{
	// [[$qid, $cDate, $mDate, $qHTML, $desc], ...]
	$retv = Array();
	$stmt = $db->prepare(QUERY_SELECT_qid_NotAnsweredBy_uid);
	$stmt->bind_param(QUERY_SELECT_qid_NotAnsweredBy_uid_TYPES, $uid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno == 0){
		$stmt->bind_result($rawqid, $cDate, $mDate, $qHTML, $desc);
		while($stmt->fetch()){
			$qid = getFormedUUIDString($rawqid);
			$retv[] = Array($qid, $cDate, $mDate, $qHTML, $desc);
		}
	}
	$stmt->close();
	return $retv;
}

function db_getDescriptionForQuestionBy_qid($db, $qid)
{
	$qinfo = db_getQuestionDataBy_qid($db, $qid);
	if($qinfo){
		return $qinfo[2];
	}
	return $qid;
}

function getUserIDByUserInfo($db, $userClass, $userNumberInClass)
{
	// 戻り値はuid文字列
	// 該当ユーザーが存在しない場合はnull
	$stmt = $db->prepare(QUERY_SELECT_User_uid);
	$stmt->bind_param(QUERY_SELECT_User_uid_TYPES, $userClass, $userNumberInClass);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		print $stmt->error;
		return null;
	}
	//
	$uid = null;
	$stmt->bind_result($uid);
	if($stmt->num_rows !== 0){
		$stmt->fetch();
		$uid = getFormedUUIDString($uid);
	}
	$stmt->close();
	return $uid;
}

function getUserIdentifierStringByUserID($db, $uid)
{
	// ユーザーのクラス番号名前IDを含む文字列を取得・生成し返す。
	// 存在しないユーザーはnullを返す。	
	$stmt = $db->prepare(QUERY_SELECT_User_info_BY_uid);
	$stmt->bind_param(QUERY_SELECT_User_info_BY_uid_TYPES, $uid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		print $stmt->error;
		return null;
	}
	$retv = null;
	$stmt->bind_result($userClass, $userNumberInClass, $userName);
	if($stmt->num_rows !== 0){
		$stmt->fetch();
		$retv = $userClass . " " . $userNumberInClass . " " . $userName . " (ID: " . $uid . ")";
	}
	$stmt->close();
	return $retv;
}

function getUserShortIdentifierStringByUserID($db, $uid)
{
	// ユーザーのクラス番号名前を含む文字列を取得・生成し返す。
	// 存在しないユーザーはnullを返す。	
	$stmt = $db->prepare(QUERY_SELECT_User_info_BY_uid);
	$stmt->bind_param(QUERY_SELECT_User_info_BY_uid_TYPES, $uid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		print $stmt->error;
		return null;
	}
	$retv = null;
	$stmt->bind_result($userClass, $userNumberInClass, $userName);
	if($stmt->num_rows !== 0){
		$stmt->fetch();
		$retv = getUserShortIdentifierStringByUserInfo($userClass, $userNumberInClass, $userName);
	}
	$stmt->close();
	return $retv;
}

function getUserShortIdentifierStringByUserInfo($userClass, $userNumberInClass, $userName)
{
	return ($userClass . " " . $userNumberInClass . " " . $userName);
}

function getAllUserInfoList($db)
{
	// [[uid, shortIDStr, class, number, name], ...]
	$stmt = $db->prepare(QUERY_SELECT_ALL_User);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$stmt->close();
		return null;
	}
	$stmt->bind_result($uid, $userClass, $userNumberInClass, $userName);
	$list = Array();
	while ($stmt->fetch()) {
		$list[] = Array(getFormedUUIDString($uid), getUserShortIdentifierStringByUserInfo($userClass, $userNumberInClass, $userName), $userClass, $userNumberInClass, $userName);
    }
	$stmt->close();
	return $list;
}

function getUserInfoListOfClassID($db, $cid)
{
	// [[uid, shortIDStr, class, number, name], ...]
	$stmt = $db->prepare(QUERY_SELECT_User_info_BY_cid);
	$stmt->bind_param(QUERY_SELECT_User_info_BY_cid_TYPES, $cid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		$stmt->close();
		return null;
	}
	$stmt->bind_result($uid, $userClass, $userNumberInClass, $userName);
	$list = Array();
	while ($stmt->fetch()) {
		$list[] = Array(getFormedUUIDString($uid), getUserShortIdentifierStringByUserInfo($userClass, $userNumberInClass, $userName), $userClass, $userNumberInClass, $userName);
    }
	$stmt->close();
	return $list;
}

//
// Get and Echo
//

function echoUserTable($db)
{
	print "<form method='POST' action='./admin.php'>";
	echoUserTableSub($db);
	print<<<EOF
	<input type="hidden" name="action" value="deluserid" />
	<input class="btn btn-danger" type="submit" value="選択したユーザーを削除" />
EOF;
	print "</form>";
}

function echoUserTableSub($db)
{
	// 現在のユーザーデータをHTML表形式で出力
	// formの内部のみ
	$stmt = $db->prepare(QUERY_SELECT_ALL_User);
	if(!$stmt){
		return;
	}
	$stmt->execute();
	
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echoAlertError("echoUserTable:error");
		return;
	}
	
	$stmt->bind_result($uid, $class, $number, $namestr);
	print "<table class='table'>";
	echoHTMLTableRow(array("クラス", "番号", "名前", "ID", "PASS", "選択"));
	while($stmt->fetch()){
		$idStr = getFormedUUIDString($uid);
		echoHTMLTableRow(array($class, $number, $namestr, $idStr, calcPassForUserID($idStr), '<input type="checkbox" name="uidlist[]" value="' . $idStr . '" />'));
	}
	print "</table>";
	$stmt->close();

}

function echoClassTableForCheck($db)
{
	// 現在の問題データをHTML表形式で出力
	$stmt = $db->prepare(QUERY_SELECT_ALL_Class);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echoAlertError("echoClassTableForCheck:error");
		return;
	}
	
	$stmt->bind_result($className, $cid);
	$linkbase = getCurrentPageURLWithoutArgs() . "?action=selectqidforchklst&cid=";
	print('<div class="list-group">');
	while($stmt->fetch()){
		$cidStr = getFormedUUIDString($cid);
		$printstr = '<a href="' . $linkbase . $cidStr . '" class="list-group-item">' . $className . '</a>';
		print($printstr);
	}
	print('</div>');
	$stmt->close();
}

function echoQuestionTable($db)
{
print "<form method='POST' action='./admin.php'>";
	echoQuestionTableSub($db);
print<<<EOF
	選択した問題を:<br />
	<input type="radio" name="action" value="editqid" checked />編集<br />
	<input type="radio" name="action" value="delqid" />削除<br />
	<input class="btn btn-primary" type="submit" value="実行" />
</form>
EOF;
}

function echoQuestionTableSub($db)
{
	// 現在のユーザーデータをHTML表形式で出力
	// formの内部のみ
	$stmt = $db->prepare(QUERY_SELECT_ALL_Question);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echoAlertError("echoQuestionTableSub:error");
		return;
	}
	
	$stmt->bind_result($qid, $cDate, $mDate, $qHTML, $desc);
print<<<EOF
	<table class="table">
EOF;
	echoHTMLTableRow(array("作成日時", "説明", "変更日時", "ID"));
	while($stmt->fetch()){
		$idStr = getFormedUUIDString($qid);
		echoHTMLTableRow(array($cDate, $desc, $mDate, $idStr, '<input type="radio" name="qid" value="' . $idStr . '" />'));
	}
print<<<EOF
	</table>
EOF;
	$stmt->close();
}

function echoQuestionTableForAnswerView($db, $forAdmin=false)
{
	// 現在の問題データをHTML表形式で出力
	$stmt = $db->prepare(QUERY_SELECT_ALL_Question);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echoAlertError("echoQuestionTableForAnswerView:error");
		return;
	}
	
	$stmt->bind_result($qid, $cDate, $mDate, $qHTML, $desc);
	if($forAdmin){
		print('<p>合計' . $stmt->num_rows . '件</p>');
	}
	
	$linkbase = getCurrentPageURLWithoutArgs() . "?action=viewans&qid=";
	print('<div class="list-group">');
	while($stmt->fetch()){
		$qidStr = getFormedUUIDString($qid);
		$printstr = '<a href="' . $linkbase . $qidStr . '" class="list-group-item">' . $desc . '　　<small>' . $cDate . '作成　　';
		if($forAdmin){
			$printstr .= $mDate . '更新　　' . $qidStr;
		}
		$printstr .= '</small></a>';
		print($printstr);
	}
	print('</div>');
	$stmt->close();
}

function echoAnswerForQuestionID($db, $qid, $noUserID = false)
{
	// 表示するデータの変数
	$qDescription = null;
	$ansListByKey = array();
	$ansListByUID = array();
	
	// DBから問題データを取得
	$stmt = $db->prepare(QUERY_SELECT_Question_data_BY_qid);
	$stmt->bind_param(QUERY_SELECT_Question_data_BY_qid_TYPES, $qid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echo($stmt->error);
	}
	$stmt->bind_result($qHTML, $qDescription);
	
	if($stmt->num_rows !== 0){
		$stmt->fetch();
	} else{
		echoAlertError("echoAnswerTableForQuestionIDForPublic: internal error.");
		return;
	}
	$stmt->close();

	// 回答項目名取得
	$stmt = $db->prepare(QUERY_SELECT_Answer_keyName_BY_qid);
	$stmt->bind_param(QUERY_SELECT_Answer_keyName_BY_qid_TYPES, $qid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echoAlertError("echoAnswerTableForQuestionID:error");
		return;
	}
	$stmt->bind_result($key);
	$keyOptList = array();
	while($stmt->fetch()){
		$keyOptList[$key] = getAnswerKeyNameOptionList($key);
	}
	$stmt->close();

	// 回答データ取得
	$stmt = $db->prepare(QUERY_SELECT_Answer_BY_qid);
	$stmt->bind_param(QUERY_SELECT_Answer_BY_qid_TYPES, $qid);
	$stmt->execute();
	//
	$stmt->store_result();
	if($stmt->errno != 0){
		echoAlertError("echoAnswerTableForQuestionID:error");
		return;
	}
	
	$stmt->bind_result($uid, $key, $value);
	while($stmt->fetch()){
		$ansListByKey[$key][$uid] = $value;
		$ansListByUID[$uid][$key] = $value;
	}
	$stmt->close();
	
	// 回答項目優先度ソート
	usort($keyOptList, "keyOptList_sortFunc");
	
	// HTML出力
	print<<<EOF
<div class="page-header">
  <h3>{$qDescription}　　<small>{$qid}</small></h3>
</div>
EOF;

	// 表形式以外の表示形式
	foreach($keyOptList as $keyOpt){
		if(isset($keyOpt[2]) && $keyOpt[2] === "pi"){
			// 円グラフ
			$ansSubList = $ansListByKey[$keyOpt[0]];
			$piChartSubList = array();
			foreach($ansSubList as $ans){
				if(array_key_exists($ans, $piChartSubList)){
					$piChartSubList[$ans]++;
				} else{
					$piChartSubList[$ans] = 1;
				}
			}
			if($piChartSubList){
				echoPieChart($keyOpt[1], $piChartSubList);
			}
		}
	}
	
	// 表ヘッダ部分
	echo('<table class="table">');
	echo("<tr>");
	if(!$noUserID){
		echo("<th>ユーザー</th>");
	}
	foreach($keyOptList as $keyOpt){
		echo("<th>" . $keyOpt[1] . "</th>");
	}
	echo("</tr>");
	// 表本体
	foreach($ansListByUID as $uid => $anstags){
		echo("<tr>");
		if(!$noUserID){
			echo("<td>" . getUserShortIdentifierStringByUserID($db, $uid) . "</td>");
		}
		foreach($keyOptList as $keyOpt){
			if(array_key_exists($keyOpt[0], $ansListByUID[$uid])){
				echo("<td>" . $ansListByUID[$uid][$keyOpt[0]] . "</td>");
			} else{
				echo("<td>(値なし)</td>");
			}
		}
		echo("</tr>");
	}
	echo("</table>");
}
*/
?>