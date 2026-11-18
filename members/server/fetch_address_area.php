<?php
header('Content-Type: application/json; charset=utf-8');

// connect to the database 
require_once("../../includes/config/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!empty($_POST['divisionId'])) {
    $division_id = $_POST['divisionId'];
    $district_query = $conn->prepare("SELECT * FROM area_district WHERE division_code = ?");
    $district_query->bind_param('s', $division_id);
    $district_query->execute();

    $district = $district_query->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($district, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else if (!empty($_POST['districtId'])) {
    $district_id = $_POST['districtId'];
    $upazila_query = $conn->prepare('SELECT * FROM area_upazilla WHERE district_code = ?');
    $upazila_query->bind_param('s', $district_id);
    $upazila_query->execute();

    $upazila = $upazila_query->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($upazila, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} elseif (!empty($_POST['upazilaId'])) {
    $upazila_id = $_POST['upazilaId'];

    $postOffice_query = $conn->prepare("SELECT * FROM area_postoffice WHERE p_upazila_code = ?");
    $postOffice_query->bind_param('s', $upazila_id);
    $postOffice_query->execute();

    $postOffice = $postOffice_query->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($postOffice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
