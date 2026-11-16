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
} elseif (!empty($_POST['postDivId'])) {
    $post_div_id = $_POST['postDivId'];
    $div_name = '';

    // define division name 
    switch ((int) $post_div_id) {
        case 10:
            $div_name = 'Barisal';
            break;
        case 20:
            $div_name = 'Chittagong';
            break;
        case 30:
            $div_name = 'Dhaka';
            break;
        case 40:
            $div_name = 'Khulna';
            break;
        case 45:
            $div_name = 'Mymensingh';
            break;
        case 50:
            $div_name = 'Rajshahi';
            break;
        case 55:
            $div_name = 'Rangpur';
            break;
        case 60:
            $div_name = 'Sylhet';
            break;
        default:
            $div_name = '';
    }

    $post_range = [
        'Dhaka' => [1000, 1999],
        'Mymensingh' => [2000, 2499],
        'Sylhet' => [3000, 3399],
        'Chittagong' => [4000, 4999],
        'Rangpur' => [5000, 5799],
        'Rajshahi' => [5800, 6999],
        'Khulna' => [7000, 7999],
        'Barisal' => [8200, 8799]
    ];

    if (isset($post_range[$div_name])) {
        [$min, $max] = $post_range[$div_name];
        $postOffice_query = $conn->prepare("SELECT * FROM area_postoffice WHERE CAST(post_code AS UNSIGNED) BETWEEN ? AND ?");
        $postOffice_query->bind_param('ss', $min, $max);
        $postOffice_query->execute();

        $postOffice = $postOffice_query->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode($postOffice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
