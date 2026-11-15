<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

session_start();
include('../include/db_connect.php');
error_reporting(0);

if (strlen($_SESSION['login']) == 0) {
    header('location:../index.php');
} else {
    include('../include/db_connect.php');
      
    include('../module_admin/user_query.php');
 
         if ($UserAreaID == 'H01') {
            $BranchFilter = "1";
        } else {
            $BranchFilter = "work_station = '$UserAreaID'";
        }
 
    $sql = "SELECT
    `emp_gen_id`,
    `employee_id`,
    `emp_joining_date`,
    `employee_name`,
    `department`,
    department.dep_name AS DepName, 
    `designation`,
    designation.desig_name AS DesName,
    `emploee_type`,
    `fathers_name`,
    `mothers_name`,
    `religion`,
    `gender`,
    `merital_status`,
    `blood_group`,
    `empl_picture`,
    `work_station`,
    office_branch.branch_name AS BranchName,
    `empl_status`
FROM
    `empl_gen`
    LEFT JOIN pmkmfi_admin.department ON department.dep_id=empl_gen.department
    LEFT JOIN pmkmfi_admin.designation ON designation.desig_code=empl_gen.designation
    LEFT JOIN pmkmfi_admin.office_branch ON office_branch.branch_code=empl_gen.work_station
WHERE $BranchFilter
     ORDER BY employee_id DESC";
    $result = $conn_hrm->query($sql);

    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);

    $conn_hrm->close();
}
?>