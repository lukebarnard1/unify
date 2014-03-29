<?php
  //Get all the members of a group given a group_id
  include_once("../util/mysql.php");

  $dao = new DAO(false);

  $group_id = $dao->escape($_POST["group_id"]);

  $query = "SELECT user.user_id,user.user_picture,user.user_name FROM grouping JOIN user ON user.user_id=grouping.user_id WHERE grouping.group_id=\"$group_id\";";

  $dao->myquery($query);

  echo $dao->fetch_json_part(array("user_id","user_picture","user_name"));
?>
