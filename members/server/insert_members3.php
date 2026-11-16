<?php
// connect to the database 
require_once("../../includes/config/db.php");
header('Content-Type: application/json; charset=utf-8');

// THROW ERROR
ini_set('display_errors', '0');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ONLY POST REQUEST ACCEPTED 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception("Invalid Request");
}

// request pass key 
$stepNumber = $_POST['step'];
$member_db_id = (int) ($_POST['original_member_id'] ?? 0);
$trackTransaction = false;

// define input  and db columns relation for step 1,2,4 (plain input)
$fieldMap = [
    1 => [
        'member_samity_name' => 'samity_code',
        'member_primary_product' => 'primary_product_code',
        'member_name' => 'member_name',
        'member_surname' => 'member_surname',
        'member_code' => 'member_code',
        'member_father' => 'father_name',
        'member_mother' => 'mother_name',
        'member_nationality' => 'nationality',
        'member_gender' => 'gender',
        'member_dob' => 'member_dob',
        'member_age' => 'age',
        'member_marital_status' => 'marital_status',
        'member_spouse_name' => 'spouse_name',
        'member_education' => 'education',
        'member_mobile' => 'member_mobile',
        'member_admission_fee' => 'admission_fee',
        'member_smart_id' => 'smart_id',
        'member_nid' => 'nid',
        'member_birth_reg' => 'birth_reg',
        'member_tin_no' => 'tin_no',
        'member_passport' => 'passport_no',
        'member_pass_expiry' => 'passport_expiry',
        'member_driving_lic' => 'driving_license',
        'member_drive_expiry' => 'driving_expiry',
        'member_admission_no' => 'admission_no',
        'member_application_no' => 'application_no',
    ],

    2 => [

        // nid 
        'member_NID_division' => 'nid_division',
        'member_NID_district' => 'nid_district',
        'member_NID_Upazila' => 'nid_upazila',
        'member_NID_union' => 'nid_union',
        'member_NID_village' => 'nid_village',
        'member_NID_house' => 'nid_house',

        // present  
        'member_present_division' => 'present_division',
        'member_present_district' => 'present_district',
        'member_present_Upazila' => 'present_upazila',
        'member_present_union' => 'present_union',
        'member_present_village' => 'present_village',
        'member_present_house' => 'present_house',

        // permanent 
        'member_permanent_division' => 'permanent_division',
        'member_permanent_district' => 'permanent_district',
        'member_permanent_Upazila' => 'permanent_upazila',
        'member_permanent_union' => 'permanent_union',
        'member_permanent_village' => 'permanent_village',
        'member_permanent_house' => 'permanent_house',

        // contact 
        'member_family_contact' => 'family_contact',
        'member_permanent_contact' => 'permanent_contact',
    ],

    4 => [
        'member_type' => 'member_type',
        'member_profession' => 'profession',
        'member_religion' => 'religion',
        'member_sp_category' => 'special_category',
        'member_family_member' => 'family_members',
        'member_yearly_income' => 'yearly_income',
        'member_land_area' => 'land_area',
        'member_permanent_resident' => 'permanent_resident',
        'member_fixed_asset' => 'fixed_asset',
        'member_note' => 'note',
    ],
];

// define required field 
$requiredField = [
    1 => [
        'member_samity_name',
        'member_primary_product',
        'member_name',
        'member_surname',
        'member_code',
        'member_father',
        'member_mother',
        'member_nationality',
        'member_gender',
        'member_dob',
        'member_age',
        'member_marital_status',
        'member_education',
        'member_mobile',
        'member_admission_fee',
    ],

    2 => [

        // nid 
        'member_NID_division',
        'member_NID_district',
        'member_NID_village',
        'member_NID_house',

        // present  
        'member_present_division',
        'member_present_district',
        'member_present_village',
        'member_present_house',

        // permanent 
        'member_permanent_division',
        'member_permanent_district',
        'member_permanent_village',
        'member_permanent_house',

        // contact 
        'member_permanent_contact',
    ],

    4 => [
        'member_type',
        'member_profession',
        'member_religion',
        'member_permanent_resident',
    ],
];

try {
    // FINAL STEP (on click save & submit button)
    if ($stepNumber === 'final') {
        if (!$member_db_id) {
            throw new Exception("Member not created Yet");
        }

        // find member 
        $member_query = $conn->prepare("SELECT current_step FROM members_list WHERE id = ?");
        $member_query->bind_param('i', $member_db_id);
        $member_query->execute();
        $member_query_result = $member_query->get_result();
        $member = $member_query_result->fetch_assoc();

        if (!$member) {
            throw new Exception("Member not found.");
        }

        if ((int) $member['current_step'] < 5) {
            throw new Exception("Please complete all steps first.");
        }

        // update the status  
        $status_query = $conn->prepare("UPDATE members_list SET status='submitted' WHERE id = ?");
        $status_query->bind_param('i', $member_db_id);
        $status_query->execute();

        echo json_encode(
            [
                'success' => true,
                'original_member_id' => $member_db_id
            ]
        );
        exit();
    }

    // step validation 
    $stepNo = (int) $stepNumber;
    if ($stepNo < 1 || $stepNo > 5) {
        throw new Exception("Invalid Step.");
    }

    // an existing draft must exist and must not be submitted yet
    if ($member_db_id) {
        $checkStepStatusQuery = $conn->prepare("SELECT status FROM members_list WHERE id = ?");
        $checkStepStatusQuery->bind_param('i', $member_db_id);
        $checkStepStatusQuery->execute();
        $checkStepStatusQueryResult = $checkStepStatusQuery->get_result();
        $checkStepStatus = $checkStepStatusQueryResult->fetch_assoc();
        if (!$checkStepStatus) {
            throw new Exception("Member Not Found");
        }
        if ($checkStepStatus['status'] === 'submitted') {
            throw new Exception("This admission is already submitted.");
        }
    } elseif ($stepNo !== 1) {
        throw new Exception("Please complete Member Info first.");
    }

    // STEPS 1(member info),2(address),4(additional info) (plain inputs)
    if (isset($fieldMap[$stepNo])) {

        // check required input 
        foreach ($requiredField[$stepNo] ?? [] as $fieldName) {
            if (trim($_POST[$fieldName ?? '']) === '') {
                throw new Exception("Missing required field: $fieldName");
            }
        }

        // ready the data array inputs value with related db cols relation (for step 1,2,4 the plain inputs)
        $data = [];
        foreach ($fieldMap[$stepNo] as $fieldName => $dbCol) {
            $value = trim((string) ($_POST[$fieldName] ?? ''));
            $data[$dbCol] = $value !== "" ? $value : null;
        }

        if ($stepNo === 1) {
            // valid : mobile number 
            if (!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $data['member_mobile'])) {
                throw new Exception("Members mobile number is Invalid!");
            }

            if ($data['smart_id'] === null && $data['nid'] === null) {
                throw new Exception('Please provide the SMART ID or the NID.');
            }

            // valid : smart id
            if ($data['smart_id'] !== null && !preg_match('/^\d{10}$/', $data['smart_id'])) {
                throw new Exception("Smart Id must be 10 digits.");
            }

            // valid : nid
            if ($data['nid'] !== null && !preg_match('/^\d{17}$/', $data['nid'])) {
                throw new Exception("NID must be 17 digits.");
            }

            // spouse name only for married/widow 
            if (!in_array($data['marital_status'], ['Married', 'Widow'], true)) {
                $data['spouse_name'] = null;
            } elseif ($data['spouse_name'] === null) {
                throw new Exception('Spouse name is required.');
            }
        }

        if ($stepNo === 1 && !$member_db_id) {
            // INSERT (happens only once per member)
            $data['admission_date'] = date('Y-m-d');
            $data['admission_fee'] = 10;

            $cols         = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));

            $conn->begin_transaction();
            $trackTransaction = true;

            $insert_step1_query = $conn->prepare("INSERT INTO members_list ($cols) VALUES ($placeholders)");
            $insert_step1_query->execute(array_values($data));
            $member_db_id = (int) $conn->insert_id;

            $gen_org_mem_code = 'M' . str_pad((string) $member_db_id, 7, '0', STR_PAD_LEFT);
            $updateOrigMemCodQuery = $conn->prepare("UPDATE members_list SET OriginalMemberCode = ? WHERE id = ?");
            $updateOrigMemCodQuery->bind_param("si", $gen_org_mem_code, $member_db_id);
            $updateOrigMemCodQuery->execute();

            $conn->commit();
            $trackTransaction = false;
        } else {
            // update 
            $dbColsSet = implode('= ?,', array_keys($data)) . "= ?";
            $colValuesSet = array_values($data);

            $update_step1_query = $conn->prepare("UPDATE members_list SET $dbColsSet WHERE id = '$member_db_id'");
            $update_step1_query->execute($colValuesSet);
        }
    }

    // STEP 3:: Nominee & Reference (multi row store json)
    if ($stepNo === 3) {
        $nominees = [];
        $totalShare = 0;
        foreach ($_POST['nominee_name'] ?? [] as $idx => $name) {
            $nomineeName = trim($name);
            $nomineeMobile = trim($_POST['nominee_mobile'][$idx] ?? '');
            $nomineeRelation = trim($_POST['nominee_relation'][$idx] ?? '');
            $nomineeShare = trim($_POST['nominee_share'][$idx] ?? 0);

            if ($nomineeName === '' || $nomineeRelation === '') {
                throw new Exception("Nominee name and relation are required.");
            }

            if (!preg_match('/^(?:01[3-9]\d{8}|8801[3-9]\d{8})$/', $nomineeMobile)) {
                throw new Exception("Invalid nominee mobile: $nomineeMobile");
            }
            if ($nomineeShare < 0 || $nomineeShare > 100) {
                throw new Exception("Nominee share must be 0-100.");
            }

            $totalShare = $totalShare +  $nomineeShare;
            $nominees[] = ['name' => $nomineeName, "mobile" =>  $nomineeMobile, 'relation' => $nomineeRelation, 'share' => $nomineeShare];
        }

        if (!$nominees) {
            throw new Exception("At least one nominee is required.");
        }

        if ($totalShare !== 100) {
            throw new Exception("Total nominee share must be 100% (got $totalShare%).");
        }

        // reference 
        $references = [];
        foreach ($_POST['reference_name'] ?? [] as $idx => $name) {
            $refName = trim($name);
            $refDesignation = trim($_POST['reference_designation'][$idx] ?? '');
            if ($refName === '') {
                continue;
            }
            $references[] = ["name" =>  $refName, "designation" => $refDesignation];
        }

        // ready the json to store database 
        $encodedNominees = json_encode($nominees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $encodedReferences = json_encode($references, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // update nominee and reference the data column 
        $update_nominee_query = $conn->prepare("UPDATE members_list SET nominees = ?, member_references = ? WHERE id = ?");
        $update_nominee_query->bind_param('ssi', $encodedNominees, $encodedReferences, $member_db_id);
        $update_nominee_query->execute();
    }

    // STEP-5 (documents)
    $savedDocs = null;
    if ($stepNo === 5) {
        $allowedExt = [
            // Standard Web Formats
            'image/jpeg'    => 'jpg',
            'image/jpg'     => 'jpg',
            'image/png'     => 'png',
            'image/gif'     => 'gif',

            // Modern Web Formats
            'image/webp'    => 'webp',
            'image/avif'    => 'avif',
            'image/svg+xml' => 'svg',

            // Mobile & Legacy Formats
            'image/heic'    => 'heic',
            'image/heif'    => 'heif',
            'image/bmp'     => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/tiff'    => 'tiff',
            'image/x-icon'  => 'ico'
        ];

        $maxFileSize = 5 * 1024 * 1024;

        // make file path :: where to save 
        $storeFilePath = "uploads/members/$member_db_id/";
        $absDir = dirname(__DIR__) . '/' . $storeFilePath;
        if (!is_dir($absDir)) {
            mkdir($absDir, 0775, true);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $docs  = [];
        foreach ($_POST['member_doc_type'] ?? [] as $idx => $type) {
            $docType = trim($type);
            $docDescription = trim($_POST['member_doc_desc'][$idx] ?? '');
            if ($docType === '') {
                throw new Exception("Document Type is Required");
            }

            // file already uploaded earlier? it must live inside THIS member's folder
            $fileName = '';
            $existingPath = trim($_POST['existing_path'][$idx] ?? '');
            if ($existingPath !== '' && (strpos($existingPath, $storeFilePath) !== 0 || strpos($existingPath, '..') !== false)) {
                $existingPath = '';
            }

            $fileErr = $_FILES['member_doc']['error'][$idx] ?? UPLOAD_ERR_NO_FILE;
            if ($fileErr === UPLOAD_ERR_OK) {
                $tempFile = $_FILES['member_doc']['tmp_name'][$idx];

                if ($_FILES['member_doc']['size'][$idx] > $maxFileSize) {
                    throw new Exception(("File too large (max 5 MB)."));
                }

                $fileMIME = $finfo->file($tempFile);
                if (!isset($allowedExt[$fileMIME])) {
                    throw new Exception(("Invalid File Formate"));
                }

                $fileName = $docType . $member_db_id . '.' . $allowedExt[$fileMIME];
                if (!move_uploaded_file($tempFile, $absDir . $fileName)) {
                    throw new Exception('Could not store uploaded file.');
                }

                $existingPath = $storeFilePath . $fileName;
            } elseif ($fileErr !== UPLOAD_ERR_NO_FILE) {
                throw new Exception('File upload failed (code ' . $fileErr . ').');
            }

            if ($existingPath === '') {
                throw new Exception('Please choose a file for document row ' . ($idx + 1) . '.');
            }
            $docs[] = ['type' => $docType, "description" => $docDescription, "file" => $fileName, "path" => $existingPath];
        }

        if (!$docs) {
            throw new Exception('At least one document is required.');
        }

        $encodedDocs = json_encode($docs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $update_doc_query = $conn->prepare("UPDATE members_list SET documents = ? WHERE id = ?");
        $update_doc_query->bind_param('si', $encodedDocs, $member_db_id);
        $update_doc_query->execute();
        $savedDocs = $docs;
    }


    $update_step_query = $conn->prepare("UPDATE members_list SET current_step = GREATEST(current_step,?) WHERE id = ?");
    $update_step_query->bind_param('ii', $stepNo, $member_db_id);
    $update_step_query->execute();

    $respondedJson = ['success' => true, "original_member_id" => $member_db_id];

    if ($savedDocs !== null) {
        $respondedJson['documents'] = $savedDocs;
    }

    echo json_encode($respondedJson, JSON_UNESCAPED_UNICODE);
} catch (Exception $err) {
    if ($trackTransaction) {
        $conn->rollback();
    }
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $err->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $err) {
    //  if ($trackTransaction) {
    //     $conn->rollback();
    // }
    // error_log($err->getMessage());
    // http_response_code(500);
    // echo json_encode(['success' => false, 'message' => 'Server error. Please try again.'], JSON_UNESCAPED_UNICODE);
    // exit;

    if ($trackTransaction) {
        $conn->rollback();
    }
    $isUserError = $err instanceof Exception && !($err instanceof mysqli_sql_exception);
    http_response_code($isUserError ? 422 : 500);
    if (!$isUserError) {
        error_log($err->getMessage());
    }
    echo json_encode([
        'success' => false,
        'message' => $isUserError ? $err->getMessage() : 'Server error. Please try again.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
