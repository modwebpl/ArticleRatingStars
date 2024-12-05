<?php
session_start();
require_once __DIR__ . '/../../wp-load.php';
require_once __DIR__ . '/../../FormRequest.php';
global $wpdb;

$request = new FormRequest('json');
if(!$request->get('art_id')) die(json_encode(['status' => false, 'msg' => 'invalid token']));
$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}art_rate WHERE art_id = ".intval($request->get('art_id')), OBJECT);
