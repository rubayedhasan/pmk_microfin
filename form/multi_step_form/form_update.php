<?php
session_start();
include('../include/db_connect.php');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to browser, log them

header('Content-Type: application/json');

// Session Check
if (empty($_SESSION['login'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    exit;
}

// HELPERS
function clean($conn, $val)
{
    return mysqli_real_escape_string($conn, trim($val ?? ''));
}

function deleteFileIfExists($path)
{
    if (!empty($path) && file_exists($path) && is_file($path)) {
        @unlink($path);
    }
}

// GET employee_id (must already exist - this is an UPDATE)
$employee_id = clean($conn_hrm, $_POST['employee_id'] ?? '');

if (empty($employee_id)) {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required.']);
    exit;
}

// Confirm employee actually exists
$check = mysqli_query($conn_hrm, "SELECT emp_gen_id, empl_picture FROM empl_gen WHERE employee_id = '$employee_id' LIMIT 1");
if (!$check || mysqli_num_rows($check) === 0) {
    echo json_encode(['success' => false, 'message' => "Employee ID '$employee_id' not found. Cannot update."]);
    exit;
}
$existingGen = mysqli_fetch_assoc($check);
$old_empl_picture = $existingGen['empl_picture'] ?? '';

// HANDLE EMPLOYEE PICTURE UPLOAD (Step 2) - only replace if a new file was sent
$empl_picture = $old_empl_picture; // keep old by default
if (isset($_FILES['empl_picture']) && $_FILES['empl_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'employee_upload/employees/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);

    $ext = strtolower(pathinfo($_FILES['empl_picture']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid picture format. Allowed: JPG, PNG, GIF, WEBP.']);
        exit;
    }

    $filename = 'emp_' . $employee_id . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['empl_picture']['tmp_name'], $destination)) {
        deleteFileIfExists($uploadDir . $old_empl_picture);
        $empl_picture = $filename;
    }
}

// START TRANSACTION
mysqli_begin_transaction($conn_hrm);

try {

    // STEP 1 + STEP 2 -> UPDATE empl_gen
    $emp_joining_date = clean($conn_hrm, $_POST['emp_joining_date'] ?? '');
    $employee_name = clean($conn_hrm, $_POST['employee_name'] ?? '');
    $department = clean($conn_hrm, $_POST['department'] ?? '');
    $designation = clean($conn_hrm, $_POST['designation'] ?? '');
    $emploee_type = clean($conn_hrm, $_POST['emploee_type'] ?? '');
    $work_station = clean($conn_hrm, $_POST['work_station'] ?? '');
    $project_name = clean($conn_hrm, $_POST['project_name'] ?? '');
    $probation_period = clean($conn_hrm, $_POST['probation_period'] ?? '');
    $date_conf = clean($conn_hrm, $_POST['date_conf'] ?? '');
    $security_money = clean($conn_hrm, $_POST['security_money'] ?? '0');
    $deposit_money = clean($conn_hrm, $_POST['deposit_money'] ?? '0');
    $emp_note = clean($conn_hrm, $_POST['emp_note'] ?? '');

    $fathers_name = clean($conn_hrm, $_POST['fathers_name'] ?? '');
    $mothers_name = clean($conn_hrm, $_POST['mothers_name'] ?? '');
    $religion = clean($conn_hrm, $_POST['religion'] ?? '');
    $gender = clean($conn_hrm, $_POST['gender'] ?? '');
    $merital_status = clean($conn_hrm, $_POST['merital_status'] ?? '');
    $blood_group = clean($conn_hrm, $_POST['blood_group'] ?? '');
    $empl_status = clean($conn_hrm, $_POST['empl_status'] ?? '');

    $sql_gen = "UPDATE empl_gen SET
        emp_joining_date = " . (empty($emp_joining_date) ? "NULL" : "'$emp_joining_date'") . ",
        employee_name = '$employee_name',
        department = '$department',
        designation = '$designation',
        emploee_type = '$emploee_type',
        work_station = '$work_station',
        project_name = '$project_name',
        probation_period = '$probation_period',
        date_conf = " . (empty($date_conf) ? "NULL" : "'$date_conf'") . ",
        security_money = '$security_money',
        deposit_money = '$deposit_money',
        emp_note = '$emp_note',
        fathers_name = '$fathers_name',
        mothers_name = '$mothers_name',
        religion = '$religion',
        gender = '$gender',
        merital_status = '$merital_status',
        blood_group = '$blood_group',
        empl_picture = '$empl_picture',
        empl_status = '$empl_status'
        WHERE employee_id = '$employee_id'";

    if (!mysqli_query($conn_hrm, $sql_gen)) {
        throw new Exception('Step 1&2 (empl_gen) Error: ' . mysqli_error($conn_hrm));
    }


    // STEP 3 -> UPDATE or INSERT empl_identity
    $national_id = clean($conn_hrm, $_POST['national_id'] ?? '');
    $birth_id = clean($conn_hrm, $_POST['birth_id'] ?? '');
    $passport_no = clean($conn_hrm, $_POST['passport_no'] ?? '');
    $driving_license = clean($conn_hrm, $_POST['driving_license'] ?? '');
    $tin_no = clean($conn_hrm, $_POST['tin_no'] ?? '');
    $mobile_no = clean($conn_hrm, $_POST['mobile_no'] ?? '');
    $email_id = clean($conn_hrm, $_POST['email_id'] ?? '');
    $nationality = clean($conn_hrm, $_POST['nationality'] ?? '');
    $date_of_birth = clean($conn_hrm, $_POST['date_of_birth'] ?? '');

    $resCheckIdentity = mysqli_query($conn_hrm, "SELECT empl_id FROM empl_identity WHERE empl_id = '$employee_id' LIMIT 1");
    $identityExists = $resCheckIdentity && mysqli_num_rows($resCheckIdentity) > 0;

    if ($identityExists) {
        $sql_identity = "UPDATE empl_identity SET
            national_id = '$national_id',
            birth_id = '$birth_id',
            passport_no = '$passport_no',
            driving_license = '$driving_license',
            tin_no = '$tin_no',
            mobile_no = '$mobile_no',
            email_id = '$email_id',
            nationality = '$nationality',
            date_of_birth = " . (empty($date_of_birth) ? "NULL" : "'$date_of_birth'") . "
            WHERE empl_id = '$employee_id'";
    } else {
        $sql_identity = "INSERT INTO empl_identity (
            empl_id, national_id, birth_id, passport_no, driving_license,
            tin_no, mobile_no, email_id, nationality, date_of_birth
        ) VALUES (
            '$employee_id', '$national_id', '$birth_id', '$passport_no', '$driving_license',
            '$tin_no', '$mobile_no', '$email_id', '$nationality',
            " . (empty($date_of_birth) ? "NULL" : "'$date_of_birth'") . "
        )";
    }

    if (!mysqli_query($conn_hrm, $sql_identity)) {
        throw new Exception('Step 3 (empl_identity) Error: ' . mysqli_error($conn_hrm));
    }


    // STEP 4 -> UPDATE or INSERT empl_address
    $per_house = clean($conn_hrm, $_POST['per_house'] ?? '');
    $per_division = clean($conn_hrm, $_POST['per_division'] ?? '');
    $per_district = clean($conn_hrm, $_POST['per_district'] ?? '');
    $per_upazilla = clean($conn_hrm, $_POST['per_upazilla'] ?? '');
    $per_post = clean($conn_hrm, $_POST['per_post'] ?? '');
    $per_post_code = clean($conn_hrm, $_POST['per_post_code'] ?? '');
    $pre_house = clean($conn_hrm, $_POST['pre_house'] ?? '');
    $pre_division = clean($conn_hrm, $_POST['pre_division'] ?? '');
    $pre_district = clean($conn_hrm, $_POST['pre_district'] ?? '');
    $pre_upazilla = clean($conn_hrm, $_POST['pre_upazilla'] ?? '');
    $pre_post = clean($conn_hrm, $_POST['pre_post'] ?? '');
    $pre_post_code = clean($conn_hrm, $_POST['pre_post_code'] ?? '');

    $resCheckAddr = mysqli_query($conn_hrm, "SELECT empl_id FROM empl_address WHERE empl_id = '$employee_id' LIMIT 1");
    $addrExists = $resCheckAddr && mysqli_num_rows($resCheckAddr) > 0;

    if ($addrExists) {
        $sql_address = "UPDATE empl_address SET
            per_house = '$per_house', per_division = '$per_division', per_district = '$per_district',
            per_upazilla = '$per_upazilla', per_post = '$per_post', per_post_code = '$per_post_code',
            pre_house = '$pre_house', pre_division = '$pre_division', pre_district = '$pre_district',
            pre_upazilla = '$pre_upazilla', pre_post = '$pre_post', pre_post_code = '$pre_post_code'
            WHERE empl_id = '$employee_id'";
    } else {
        $sql_address = "INSERT INTO empl_address (
            empl_id, per_house, per_division, per_district, per_upazilla, per_post, per_post_code,
            pre_house, pre_division, pre_district, pre_upazilla, pre_post, pre_post_code
        ) VALUES (
            '$employee_id',
            '$per_house', '$per_division', '$per_district', '$per_upazilla', '$per_post', '$per_post_code',
            '$pre_house', '$pre_division', '$pre_district', '$pre_upazilla', '$pre_post', '$pre_post_code'
        )";
    }

    if (!mysqli_query($conn_hrm, $sql_address)) {
        throw new Exception('Step 4 (empl_address) Error: ' . mysqli_error($conn_hrm));
    }


    // STEP 5 -> emp_edu : DELETE old rows, INSERT fresh rows
    if (!mysqli_query($conn_hrm, "DELETE FROM emp_edu WHERE emp_id = '$employee_id'")) {
        throw new Exception('Step 5 (emp_edu delete) Error: ' . mysqli_error($conn_hrm));
    }
    if (!empty($_POST['education']) && is_array($_POST['education'])) {
        foreach ($_POST['education'] as $edu) {
            $edu_examination = clean($conn_hrm, $edu['examination'] ?? '');
            $edu_institution = clean($conn_hrm, $edu['institution'] ?? '');
            $edu_msubject = clean($conn_hrm, $edu['major_subject'] ?? '');
            $board_university = clean($conn_hrm, $edu['board_university'] ?? '');
            $academic_year = clean($conn_hrm, $edu['academic_year'] ?? '');
            $result = clean($conn_hrm, $edu['result'] ?? '');

            if (empty($edu_examination) && empty($edu_institution) && empty($edu_msubject))
                continue;

            $sql_edu = "INSERT INTO emp_edu (
                emp_id, edu_examination, edu_institution, edu_msubject,
                board_university, academic_year, result
            ) VALUES (
                '$employee_id', '$edu_examination', '$edu_institution', '$edu_msubject',
                '$board_university', '$academic_year', '$result'
            )";

            if (!mysqli_query($conn_hrm, $sql_edu)) {
                throw new Exception('Step 5 (emp_edu insert) Error: ' . mysqli_error($conn_hrm));
            }
        }
    }


    // STEP 6 -> emp_training : DELETE old rows, INSERT fresh rows
    if (!mysqli_query($conn_hrm, "DELETE FROM emp_training WHERE emp_id = '$employee_id'")) {
        throw new Exception('Step 6 (emp_training delete) Error: ' . mysqli_error($conn_hrm));
    }
    if (!empty($_POST['training']) && is_array($_POST['training'])) {
        foreach ($_POST['training'] as $train) {
            $course_name = clean($conn_hrm, $train['course_name'] ?? '');
            $course_stard_date = clean($conn_hrm, $train['course_stard_date'] ?? '');
            $course_end_date = clean($conn_hrm, $train['course_end_date'] ?? '');
            $course_duration = clean($conn_hrm, $train['course_duration'] ?? '');
            $institution_name = clean($conn_hrm, $train['institution_name'] ?? '');
            $institution_address = clean($conn_hrm, $train['institution_address'] ?? '');
            $train_result = clean($conn_hrm, $train['result'] ?? '');

            if (empty($course_name) && empty($institution_name))
                continue;

            $sql_train = "INSERT INTO emp_training (
                emp_id, course_name, course_stard_date, course_end_date, course_duration,
                institution_name, institution_address, result
            ) VALUES (
                '$employee_id', '$course_name',
                " . (empty($course_stard_date) ? "NULL" : "'$course_stard_date'") . ",
                " . (empty($course_end_date) ? "NULL" : "'$course_end_date'") . ",
                '$course_duration', '$institution_name', '$institution_address', '$train_result'
            )";

            if (!mysqli_query($conn_hrm, $sql_train)) {
                throw new Exception('Step 6 (emp_training insert) Error: ' . mysqli_error($conn_hrm));
            }
        }
    }


    // STEP 7 -> emp_exp : DELETE old rows, INSERT fresh rows
    if (!mysqli_query($conn_hrm, "DELETE FROM emp_exp WHERE emp_id = '$employee_id'")) {
        throw new Exception('Step 7 (emp_exp delete) Error: ' . mysqli_error($conn_hrm));
    }
    if (!empty($_POST['experience']) && is_array($_POST['experience'])) {
        foreach ($_POST['experience'] as $exp) {
            $org_name = clean($conn_hrm, $exp['org_name'] ?? '');
            $exp_project_name = clean($conn_hrm, $exp['project_name'] ?? '');
            $company_location = clean($conn_hrm, $exp['company_location'] ?? '');
            $from_date = clean($conn_hrm, $exp['from_date'] ?? '');
            $to_date = clean($conn_hrm, $exp['to_date'] ?? '');
            $job_respons = clean($conn_hrm, $exp['job_respons'] ?? '');

            if (empty($org_name))
                continue;

            $sql_exp = "INSERT INTO emp_exp (
                emp_id, org_name, project_name, company_location, from_date, to_date, job_respons
            ) VALUES (
                '$employee_id', '$org_name', '$exp_project_name', '$company_location',
                " . (empty($from_date) ? "NULL" : "'$from_date'") . ",
                " . (empty($to_date) ? "NULL" : "'$to_date'") . ",
                '$job_respons'
            )";

            if (!mysqli_query($conn_hrm, $sql_exp)) {
                throw new Exception('Step 7 (emp_exp insert) Error: ' . mysqli_error($conn_hrm));
            }
        }
    }


    // STEP 8 -> UPDATE or INSERT empl_guerontor (now includes picture upload)
    $guerontor_name = clean($conn_hrm, $_POST['guerontor_name'] ?? '');
    $guar_nid_passport = clean($conn_hrm, $_POST['nid_passport'] ?? '');
    $guar_dob = clean($conn_hrm, $_POST['guar_date_of_birth'] ?? '');
    $guar_gender = clean($conn_hrm, $_POST['guar_gender'] ?? '');
    $phone_no = clean($conn_hrm, $_POST['phone_no'] ?? '');
    $guar_email = clean($conn_hrm, $_POST['guar_email_id'] ?? '');
    $permanent_address = clean($conn_hrm, $_POST['permanent_address'] ?? '');
    $present_address = clean($conn_hrm, $_POST['present_address'] ?? '');
    $occupation = clean($conn_hrm, $_POST['occupation'] ?? '');

    $resCheckGuar = mysqli_query($conn_hrm, "SELECT empl_id, picture FROM empl_guerontor WHERE empl_id = '$employee_id' LIMIT 1");
    $guarExists = $resCheckGuar && mysqli_num_rows($resCheckGuar) > 0;
    $old_guar_picture = '';
    if ($guarExists) {
        $rowGuarOld = mysqli_fetch_assoc($resCheckGuar);
        $old_guar_picture = $rowGuarOld['picture'] ?? '';
    }

    $guar_picture = $old_guar_picture;
    if (isset($_FILES['guar_picture']) && $_FILES['guar_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'employee_upload/guarantors/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['guar_picture']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'guar_' . $employee_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['guar_picture']['tmp_name'], $uploadDir . $filename)) {
                deleteFileIfExists($uploadDir . $old_guar_picture);
                $guar_picture = $filename;
            }
        }
    }

    if (!empty($guerontor_name)) {
        if ($guarExists) {
            $sql_guar = "UPDATE empl_guerontor SET
                guerontor_name = '$guerontor_name',
                nid_passport = '$guar_nid_passport',
                date_of_birth = " . (empty($guar_dob) ? "NULL" : "'$guar_dob'") . ",
                gender = '$guar_gender',
                phone_no = '$phone_no',
                email_id = '$guar_email',
                permanent_address = '$permanent_address',
                present_address = '$present_address',
                occupation = '$occupation',
                picture = '$guar_picture'
                WHERE empl_id = '$employee_id'";
        } else {
            $sql_guar = "INSERT INTO empl_guerontor (
                empl_id, guerontor_name, nid_passport, date_of_birth, gender,
                phone_no, email_id, permanent_address, present_address, occupation, picture
            ) VALUES (
                '$employee_id', '$guerontor_name', '$guar_nid_passport',
                " . (empty($guar_dob) ? "NULL" : "'$guar_dob'") . ",
                '$guar_gender', '$phone_no', '$guar_email',
                '$permanent_address', '$present_address', '$occupation', '$guar_picture'
            )";
        }

        if (!mysqli_query($conn_hrm, $sql_guar)) {
            throw new Exception('Step 8 (empl_guerontor) Error: ' . mysqli_error($conn_hrm));
        }
    }


    // STEP 9 -> emp_nominee : DELETE old rows + old pictures, INSERT fresh rows
    // NOTE: nominee picture file key format is now nominee_picture{idx} (no underscore), matching the new form's JS.
    $old_nominee_pics = [];
    $resOldNom = mysqli_query($conn_hrm, "SELECT picture FROM emp_nominee WHERE emp_id = '$employee_id'");
    if ($resOldNom) {
        while ($rowOldNom = mysqli_fetch_assoc($resOldNom)) {
            if (!empty($rowOldNom['picture'])) {
                $old_nominee_pics[] = $rowOldNom['picture'];
            }
        }
    }

    if (!mysqli_query($conn_hrm, "DELETE FROM emp_nominee WHERE emp_id = '$employee_id'")) {
        throw new Exception('Step 9 (emp_nominee delete) Error: ' . mysqli_error($conn_hrm));
    }

    $new_nominee_pics_used = [];

    if (!empty($_POST['nominee']) && is_array($_POST['nominee'])) {
        foreach ($_POST['nominee'] as $idx => $nom) {
            $nominee_name = clean($conn_hrm, $nom['nominee_name'] ?? '');
            $birth_date = clean($conn_hrm, $nom['birth_date'] ?? '');
            $contact_no = clean($conn_hrm, $nom['contact_no'] ?? '');
            $relation = clean($conn_hrm, $nom['relation'] ?? '');
            $nid_birth_reg = clean($conn_hrm, $nom['nid_birth_reg'] ?? '');
            $emp_pf = clean($conn_hrm, $nom['emp_pf'] ?? '0');
            $gratuity = clean($conn_hrm, $nom['gratuity'] ?? '0');
            $staff_welfare_fund = clean($conn_hrm, $nom['staff_welfare_fund'] ?? '0');
            $other_benifit = clean($conn_hrm, $nom['other_benifit'] ?? '0');

            if (empty($nominee_name))
                continue;

            $nom_picture = '';
            // New key format (no underscore before idx), matches updated employee_reg.php JS
            $fileKey = 'nominee_picture' . $idx;
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'employee_upload/nominees/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed)) {
                    $filename = 'nom_' . $employee_id . '_' . $idx . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $filename)) {
                        $nom_picture = $filename;
                    }
                }
            } else {
                // Keep existing picture if the field carries it (hidden input from edit page)
                $existingPicKey = 'nominee_existing_picture_' . $idx;
                if (!empty($_POST[$existingPicKey])) {
                    $nom_picture = clean($conn_hrm, $_POST[$existingPicKey]);
                }
            }

            $sql_nom = "INSERT INTO emp_nominee (
                emp_id, nominee_name, birth_date, contact_no, picture,
                relation, nid_birth_reg, emp_pf, gratuity, staff_welfare_fund, other_benifit
            ) VALUES (
                '$employee_id', '$nominee_name',
                " . (empty($birth_date) ? "NULL" : "'$birth_date'") . ",
                '$contact_no', '$nom_picture', '$relation', '$nid_birth_reg',
                '$emp_pf', '$gratuity', '$staff_welfare_fund', '$other_benifit'
            )";

            if (!mysqli_query($conn_hrm, $sql_nom)) {
                throw new Exception('Step 9 (emp_nominee insert) Error: ' . mysqli_error($conn_hrm));
            }

            if (!empty($nom_picture)) {
                $new_nominee_pics_used[] = $nom_picture;
            }
        }
    }

    // ALL OK - COMMIT
    mysqli_commit($conn_hrm);

    // Remove orphaned old nominee pictures that weren't re-used
    foreach ($old_nominee_pics as $oldPic) {
        if (!in_array($oldPic, $new_nominee_pics_used)) {
            deleteFileIfExists('employee_upload/nominees/' . $oldPic);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Employee updated successfully!',
        'employee_id' => $employee_id
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn_hrm);
    error_log('Employee Update Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn_hrm);
?>