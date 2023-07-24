<?php

function setResponseData($data = array(), $success = false, $code = false, $msg = false, $dataMsg = false)
{
  $response = array();

  // if(gettype($data) == 'array' && count($data) == 0) {
  //   $response['data'] = (object)$data;
  // } else {
  //   $response['data'] = $data;
  // }

  $response['data'] = $data;

  $response['success'] = (bool)$success;

  if($code) {
    $response['error']['code'] = $code;
  }

  if($msg) {
    $response['error']['msg'] = $msg;
  }

  if($dataMsg) {
    $response['data_msg'] = $dataMsg;
  } else {
    unset($response['data']);
  }

  return $response;
}
