<?php
session_start();
require_once __DIR__ . '/../../wp-load.php';
require_once __DIR__ . '/../../FormRequest.php';
global $wpdb;

$request = new FormRequest('json');
if(!$request->get('art_id')) die(json_encode(['status' => false, 'msg' => 'invalid token']));
$wpdb->insert($wpdb->prefix.'art_rate',['art_id' => intval($request->get('art_id')), 'rate' => intval($request->get('set_rate'))], ['%d','%d']);
$row_id = $wpdb->insert_id;

$results = $wpdb->get_results( "SELECT sum(rate) as rate, count(id) as count FROM {$wpdb->prefix}art_rate WHERE art_id = ".intval($request->get('art_id')), OBJECT);
$count = intval($results[0]->count);
$rate = number_format(intval($results[0]->rate) / intval($results[0]->count), 1, '.', '');
die(json_encode(['rate' => $rate, 'count' => intval($results[0]->count)]));
