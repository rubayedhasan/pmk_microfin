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
        'member_samity_name' => 'SamityId',
        'member_primary_product' => 'PrimaryProductId',
        'member_name' => 'Name',
        'member_surname' => 'MemberSurname',
        'member_code' => 'MemberCode',
        'member_father' => 'FathersName',
        'member_mother' => 'MothersName',
        'member_nationality' => 'Nationality',
        'member_gender' => 'Gender',
        'member_dob' => 'DateOfBirth',
        'member_age' => 'Age',
        'member_marital_status' => 'MaritalStatus',
        'member_spouse_name' => 'SpouseName',
        'member_education' => 'LastAchievedDegree',
        'member_mobile' => 'MobileNo',
        'member_admission_fee' => 'AdmissionFee',
        'member_smart_id' => 'SmartId',
        'member_nid' => 'NationalId',
        'member_birth_reg' => 'BirthRegistrationNo',
        'member_tin_no' => 'TinNo',
        'member_passport' => 'PassportNo',
        'member_pass_expiry' => 'PassportExpiryDate',
        'member_driving_lic' => 'DrivingLicense',
        'member_drive_expiry' => 'DrivingLinceseExpiryDate',
        'member_admission_no' => 'FormApplicationNo',
        'member_application_no' => 'RegistrationNo',
    ],

    2 => [

        // nid 
        'member_NID_division' => 'NidDivision',
        'member_NID_district' => 'NidDstrict',
        'member_NID_Upazila' => 'NidUpazila',
        'member_NID_post_office' => 'NidPostOfficeArea',
        'member_NID_union' => 'NidUnion',
        'member_NID_village' => 'NidVillageWard',
        'member_NID_house' => 'NidHouseStreet',

        // present  
        'member_present_division' => 'PresentDivision',
        'member_present_district' => 'PresentDistrict',
        'member_present_Upazila' => 'PresentUpazila',
        'member_present_post_office' => 'PresentPostOfficeArea',
        'member_present_union' => 'PresentUnion',
        'member_present_village' => 'PresentVillageWard',
        'member_present_house' => 'PresentHouseStreet',

        // permanent 
        'member_permanent_division' => 'PermanentDivision',
        'member_permanent_district' => 'PermanentDistrict',
        'member_permanent_Upazila' => 'PermanentUpazila',
        'member_permanent_post_office' => 'PermanentPostOfficeArea',
        'member_permanent_union' => 'PermanentUnion',
        'member_permanent_village' => 'PermanentVillageWard',
        'member_permanent_house' => 'PermanentHouseStreet',

        // contact 
        'member_family_contact' => 'PresentContactNo',
        'member_permanent_contact' => 'PermanentContactNo',
    ],

    4 => [
        'member_type' => 'MemberType',
        'member_profession' => 'Profession',
        'member_religion' => 'Religion',
        'member_sp_category' => 'Remarks',
        'member_family_member' => 'NoOfFamilyMember',
        'member_yearly_income' => 'YearlyIncome',
        'member_land_area' => 'LandArea',
        'member_permanent_resident' => 'IsMembersPermanentResidents',
        'member_fixed_asset' => 'FixedAssetInfo',
        'member_note' => 'Note',
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
        'member_NID_post_office',
        'member_NID_village',
        'member_NID_house',

        // present  
        'member_present_division',
        'member_present_district',
        'member_present_post_office',
        'member_present_village',
        'member_present_house',

        // permanent 
        'member_permanent_division',
        'member_permanent_district',
        'member_permanent_post_office',
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
        $member_query = $conn->prepare("SELECT AdmissionFormCurrentStep FROM members WHERE id = ?");
        $member_query->bind_param('i', $member_db_id);
        $member_query->execute();
        $member_query_result = $member_query->get_result();
        $member = $member_query_result->fetch_assoc();

        if (!$member) {
            throw new Exception("Member not found.");
        }

        if ((int) $member['AdmissionFormCurrentStep'] < 5) {
            throw new Exception("Please complete all steps first.");
        }

        // update the status  
        $status_query = $conn->prepare("UPDATE members SET AdmissionFormStatus = 'submitted' WHERE id = ?");
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
        $checkStepStatusQuery = $conn->prepare("SELECT AdmissionFormStatus FROM members WHERE id = ?");
        $checkStepStatusQuery->bind_param('i', $member_db_id);
        $checkStepStatusQuery->execute();
        $checkStepStatusQueryResult = $checkStepStatusQuery->get_result();
        $checkStepStatus = $checkStepStatusQueryResult->fetch_assoc();
        if (!$checkStepStatus) {
            throw new Exception("Member Not Found");
        }
        if ($checkStepStatus['AdmissionFormStatus'] === 'submitted') {
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
            if (!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', $data['MobileNo'])) {
                throw new Exception("Members mobile number is Invalid!");
            }

            if ($data['SmartId'] === null && $data['NationalId'] === null) {
                throw new Exception('Please provide the SMART ID or the NID.');
            }

            // valid : smart id
            if ($data['SmartId'] !== null && !preg_match('/^\d{10}$/', $data['SmartId'])) {
                throw new Exception("Smart Id must be 10 digits.");
            }

            // valid : nid
            if ($data['NationalId'] !== null && !preg_match('/^\d{17}$/', $data['NationalId'])) {
                throw new Exception("NID must be 17 digits.");
            }

            // spouse name only for married/widow 
            if (!in_array($data['MaritalStatus'], ['Married', 'Widow'], true)) {
                $data['SpouseName'] = null;
            } elseif ($data['SpouseName'] === null) {
                throw new Exception('Spouse name is required.');
            }
        }

        if ($stepNo === 1 && !$member_db_id) {
            // INSERT (happens only once per member)
            $data['Admission_date'] = date('Y-m-d');
            $data['AdmissionFee'] = 10;

            $cols         = implode(',', array_keys($data));
            $placeholders = implode(',', array_fill(0, count($data), '?'));

            $conn->begin_transaction();
            $trackTransaction = true;

            $insert_step1_query = $conn->prepare("INSERT INTO members ($cols) VALUES ($placeholders)");
            $insert_step1_query->execute(array_values($data));
            $member_db_id = (int) $conn->insert_id;

            $gen_org_mem_code = 'M' . str_pad((string) $member_db_id, 7, '0', STR_PAD_LEFT);
            $updateOrigMemCodQuery = $conn->prepare("UPDATE members SET OriginalMemberId = ? WHERE id = ?");
            $updateOrigMemCodQuery->bind_param("si", $gen_org_mem_code, $member_db_id);
            $updateOrigMemCodQuery->execute();

            $conn->commit();
            $trackTransaction = false;
        } else {
            // update 
            $dbColsSet = implode('= ?,', array_keys($data)) . "= ?";
            $colValuesSet = array_values($data);

            $update_step1_query = $conn->prepare("UPDATE members SET $dbColsSet WHERE id = '$member_db_id'");
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
        $update_nominee_query = $conn->prepare("UPDATE members SET MemberNominees = ?, MemberReferences = ? WHERE id = ?");
        $update_nominee_query->bind_param('ssi', $encodedNominees, $encodedReferences, $member_db_id);
        $update_nominee_query->execute();
    }

    // STEP-5 (MemberDocuments)
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

        $update_doc_query = $conn->prepare("UPDATE members SET MemberDocuments = ? WHERE id = ?");
        $update_doc_query->bind_param('si', $encodedDocs, $member_db_id);
        $update_doc_query->execute();
        $savedDocs = $docs;
    }


    $update_step_query = $conn->prepare("UPDATE members SET AdmissionFormCurrentStep = GREATEST(AdmissionFormCurrentStep,?) WHERE id = ?");
    $update_step_query->bind_param('ii', $stepNo, $member_db_id);
    $update_step_query->execute();

    $respondedJson = ['success' => true, "original_member_id" => $member_db_id];

    if ($savedDocs !== null) {
        $respondedJson['MemberDocuments'] = $savedDocs;
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
