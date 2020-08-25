<?php
require('function.php');
header("Content-Type: application/json; charset=UTF-8"); 

if(!empty($_POST['bord_id'])){

  $bord_id = (int)$_POST['bord_id'];
  $viewData = getMsgsAndBord($bord_id, false);

  if(isset($viewData[0]['msg'])){
    echo json_encode($viewData);
    exit;
  }
}