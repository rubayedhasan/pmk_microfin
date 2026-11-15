<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

//session_start();
include('../include/db_connect.php');
error_reporting(0);

if(strlen($_SESSION['login'])==0)
  { 
header('location:../index.php');
}else{
include('../include/db_connect.php');  

$sql = "SELECT 
    	office_branch.branch_id AS branchid,
    	office_branch.branch_code AS branchcode,
    	office_branch.branch_name AS branchname,
    	office_branch.branch_status AS branchstatus,
    	office_branch.area_code AS areacode,
    	office_area.area_name AS areaname,
    	office_branch.region_code AS regioncode,
    	office_region.region_name AS regionname,
    	office_branch.zone_code AS zonecode,
    	office_zone.zone_name AS zonename,
    	office_branch.branch_op_date AS branchopdate,
    	office_branch.mobile_no AS mobileno,
    	office_branch.email_id AS emailid,
    	office_branch.address AS address,
    	office_branch.division_code AS divisioncode,
    	area_division.division_name AS divisionname,
    	office_branch.district_code AS districtcode,
    	area_district.disctrict_name AS districtname,
    	office_branch.upazilla_code AS upazillacode,
    	area_upazilla.upazilla_name AS upazillaname,
      office_branch.branch_codeold AS branch_codeold,
      office_branch.branch_op_date AS branchOpDate

   FROM `office_branch` 
        LEFT JOIN office_region ON office_region.region_code=office_branch.region_code
        LEFT JOIN office_zone ON office_zone.zone_code=office_branch.zone_code
        LEFT JOIN office_area ON office_area.area_code=office_branch.area_code
        LEFT JOIN area_division ON area_division.division_code=office_branch.division_code
        LEFT JOIN area_district ON area_district.district_code=office_branch.district_code
        LEFT JOIN area_upazilla ON area_upazilla.upazilla_code=office_branch.upazilla_code
    WHERE 1";
$result = $conn_ad->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$conn_payr->close();
}
?>