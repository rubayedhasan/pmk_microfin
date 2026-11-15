<?php

/**
 * server/insert_members.php
 * Saves ONE step of the member admission form. Always answers with JSON.
 *
 * IMPORTANT: nothing (no space, no empty line, no BOM) may exist before the opening php tag above.
 */

// ---- set to false on the live server (it adds the real error to the JSON "debug" key) ----
$DEBUG = true;

ini_set('display_errors', '0');
error_reporting(E_ALL);

// Buffer all output: a notice / warning / echo from db.php is thrown away before the JSON is sent.
ob_start();

// "|" (bitwise OR). The old code used "||" which is a logical OR and turned exceptions OFF.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// error that is safe to show to the user (validation etc.)
class UserError extends Exception {}

function respond(array $payload, int $status = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// prepare + bind + execute in one go (works on every PHP 7+/8 version)
function db_run(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

// input name => db column (plain inputs of step 1, 2, 4)
$fieldMap = [
    1 => [
        'member_samity_name'     => 'samity_code',
        'member_primary_product' => 'primary_product_code',
        'member_name'            => 'member_name',
        'member_surname'         => 'member_surname',
        'member_code'            => 'member_code',
        'member_father'          => 'father_name',
        'member_mother'          => 'mother_name',
        'member_nationality'     => 'nationality',
        'member_gender'          => 'gender',
        'member_dob'             => 'member_dob',
        'member_age'             => 'age',
        'member_marital_status'  => 'marital_status',
        'member_spouse_name'     => 'spouse_name',
        'member_education'       => 'education',
        'member_mobile'          => 'member_mobile',
        'member_admission_fee'   => 'admission_fee',
        'member_smart_id'        => 'smart_id',
        'member_nid'             => 'nid',
        'member_birth_reg'       => 'birth_reg',
        'member_tin_no'          => 'tin_no',
        'member_passport'        => 'passport_no',
        'member_pass_expiry'     => 'passport_expiry',
        'member_driving_lic'     => 'driving_license',
        'member_drive_expiry'    => 'driving_expiry',
        'member_admission_no'    => 'admission_no',
        'member_application_no'  => 'application_no',
    ],

    2 => [
        // nid
        'member_NID_division' => 'nid_division',
        'member_NID_district' => 'nid_district',
        'member_NID_Upazila'  => 'nid_upazila',
        'member_NID_union'    => 'nid_union',
        'member_NID_village'  => 'nid_village',
        'member_NID_house'    => 'nid_house',

        // present
        'member_present_division' => 'present_division',
        'member_present_district' => 'present_district',
        'member_present_Upazila'  => 'present_upazila',
        'member_present_union'    => 'present_union',
        'member_present_village'  => 'present_village',
        'member_present_house'    => 'present_house',

        // permanent
        'member_permanent_division' => 'permanent_division',
        'member_permanent_district' => 'permanent_district',
        'member_permanent_Upazila'  => 'permanent_upazila',
        'member_permanent_union'    => 'permanent_union',
        'member_permanent_village'  => 'permanent_village',
        'member_permanent_house'    => 'permanent_house',

        // contact
        'member_family_contact'    => 'family_contact',
        'member_permanent_contact' => 'permanent_contact',
    ],

    4 => [
        'member_type'               => 'member_type',
        'member_profession'         => 'profession',
        'member_religion'           => 'religion',
        'member_sp_category'        => 'special_category',
        'member_family_member'      => 'family_members',
        'member_yearly_income'      => 'yearly_income',
        'member_land_area'          => 'land_area',
        'member_permanent_resident' => 'permanent_resident',
        'member_fixed_asset'        => 'fixed_asset',
        'member_note'               => 'note',
    ],
];

// required fields (kept in sync with the "required" attributes in the HTML)
$requiredField = [
    1 => [
        'member_samity_name',
        'member_primary_product',
        'member_name',
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
        'member_NID_division',
        'member_NID_district',
        'member_NID_Upazila',
        'member_NID_union',
        'member_NID_village',
        'member_NID_house',

        'member_present_division',
        'member_present_district',
        'member_present_Upazila',
        'member_present_union',
        'member_present_village',

        'member_permanent_division',
        'member_permanent_district',
        'member_permanent_Upazila',
        'member_permanent_union',
        'member_permanent_village',

        'member_family_contact',
    ],

    4 => [
        'member_type',
        'member_profession',
        'member_religion',
        'member_permanent_resident',
    ],
];

$conn = null;
$inTransaction = false;

try {
    // inside the try block: a connection error is now returned as JSON, not as an HTML error page
    require_once __DIR__ . '/../../includes/config/db.php';

    if (!($conn instanceof mysqli)) {
        throw new Exception('config/db.php must create a mysqli connection called $conn.');
    }

    // ONLY POST REQUEST ACCEPTED
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new UserError('Invalid request.');
    }

    $stepNumber   = (string) ($_POST['step'] ?? '');
    $member_db_id = (int) ($_POST['original_member_id'] ?? 0);

    // =====================================================
    // FINAL STEP (click on "Save & Submit")
    // =====================================================
    if ($stepNumber === 'final') {
        if (!$member_db_id) {
            throw new UserError('Member not created yet.');
        }

        $stmt   = db_run($conn, 'SELECT current_step, status FROM members_list WHERE id = ?', 'i', [$member_db_id]);
        $member = $stmt->get_result()->fetch_assoc();

        if (!$member) {
            throw new UserError('Member not found.');
        }
        if ((int) $member['current_step'] < 5) {
            throw new UserError('Please complete all steps first.');
        }

        db_run($conn, "UPDATE members_list SET status = 'submitted' WHERE id = ?", 'i', [$member_db_id]);

        respond(['success' => true, 'original_member_id' => $member_db_id]);
    }

    // =====================================================
    // step validation
    // =====================================================
    $stepNo = (int) $stepNumber;
    if ($stepNo < 1 || $stepNo > 5) {
        throw new UserError('Invalid step.');
    }

    if ($member_db_id) {
        // a draft already exists: it must be found and must not be submitted yet
        $stmt   = db_run($conn, 'SELECT status FROM members_list WHERE id = ?', 'i', [$member_db_id]);
        $member = $stmt->get_result()->fetch_assoc();

        if (!$member) {
            throw new UserError('Member not found.');
        }
        if ($member['status'] === 'submitted') {
            throw new UserError('This admission is already submitted.');
        }
    } elseif ($stepNo !== 1) {
        // no draft yet -> only step 1 is allowed
        // (the old code had this check on the wrong side, so every step after 1 was rejected)
        throw new UserError('Please complete Member Info first.');
    }

    // =====================================================
    // STEPS 1 (member info), 2 (address), 4 (additional info) :: plain inputs
    // =====================================================
    if (isset($fieldMap[$stepNo])) {
        foreach ($requiredField[$stepNo] ?? [] as $fieldName) {
            if (trim((string) ($_POST[$fieldName] ?? '')) === '') {
                throw new UserError("Missing required field: $fieldName");
            }
        }

        $data = [];
        foreach ($fieldMap[$stepNo] as $fieldName => $dbCol) {
            $value = trim((string) ($_POST[$fieldName] ?? ''));
            $data[$dbCol] = $value !== '' ? $value : null;
        }

        if ($stepNo === 1) {
            if (!preg_match('/^(?:\+?88)?01[3-9]\d{8}$/', (string) $data['member_mobile'])) {
                throw new UserError('Member mobile number is invalid.');
            }

            // SMART ID or NID: at least one is required (same rule as the JavaScript)
            if ($data['smart_id'] === null && $data['nid'] === null) {
                throw new UserError('Please provide the SMART ID or the NID.');
            }
            if ($data['smart_id'] !== null && !preg_match('/^\d{10}$/', $data['smart_id'])) {
                throw new UserError('Smart ID must be 10 digits.');
            }
            if ($data['nid'] !== null && !preg_match('/^\d{17}$/', $data['nid'])) {
                throw new UserError('NID must be 17 digits.');
            }

            // spouse name only for married / widow
            if (!in_array($data['marital_status'], ['Married', 'Widow'], true)) {
                $data['spouse_name'] = null;
            } elseif ($data['spouse_name'] === null) {
                throw new UserError('Spouse name is required.');
            }

            // never trust the browser for the fee
            $data['admission_fee'] = '10';
        }

        if ($stepNo === 1 && !$member_db_id) {
            // INSERT (happens only once per member)
            $data['admission_date'] = date('Y-m-d');

            $cols         = '`' . implode('`, `', array_keys($data)) . '`';
            $placeholders = implode(', ', array_fill(0, count($data), '?'));

            $conn->begin_transaction();
            $inTransaction = true;

            db_run(
                $conn,
                "INSERT INTO members_list ($cols) VALUES ($placeholders)",
                str_repeat('s', count($data)),
                array_values($data)
            );
            $member_db_id = (int) $conn->insert_id;

            $gen_org_mem_code = 'M' . str_pad((string) $member_db_id, 7, '0', STR_PAD_LEFT);
            db_run(
                $conn,
                'UPDATE members_list SET OriginalMemberCode = ? WHERE id = ?',
                'si',
                [$gen_org_mem_code, $member_db_id]
            );

            $conn->commit();
            $inTransaction = false;
        } else {
            // UPDATE
            $setParts = [];
            foreach (array_keys($data) as $dbCol) {
                $setParts[] = "`$dbCol` = ?";
            }

            $values   = array_values($data);
            $values[] = $member_db_id;

            db_run(
                $conn,
                'UPDATE members_list SET ' . implode(', ', $setParts) . ' WHERE id = ?',
                str_repeat('s', count($data)) . 'i',
                $values
            );
        }
    }

    // =====================================================
    // STEP 3 :: Nominee & Reference (multi row, stored as JSON)
    // =====================================================
    if ($stepNo === 3) {
        $nominees   = [];
        $totalShare = 0.0;

        $nomineeNames = $_POST['nominee_name'] ?? [];
        if (!is_array($nomineeNames)) {
            $nomineeNames = [];
        }

        foreach ($nomineeNames as $idx => $name) {
            $nomineeName     = trim((string) $name);
            $nomineeMobile   = trim((string) ($_POST['nominee_mobile'][$idx] ?? ''));
            $nomineeRelation = trim((string) ($_POST['nominee_relation'][$idx] ?? ''));
            // the old code was trim(['nominee_share'][$idx] ?? 0)  -> "$_POST" was missing
            $nomineeShare    = (float) ($_POST['nominee_share'][$idx] ?? 0);

            if ($nomineeName === '' || $nomineeRelation === '') {
                throw new UserError('Nominee name and relation are required.');
            }
            if (!preg_match('/^(?:01[3-9]\d{8}|8801[3-9]\d{8})$/', $nomineeMobile)) {
                throw new UserError("Invalid nominee mobile: $nomineeMobile");
            }
            if ($nomineeShare < 0 || $nomineeShare > 100) {
                throw new UserError('Nominee share must be between 0 and 100.');
            }

            $totalShare += $nomineeShare;
            $nominees[] = [
                'name'     => $nomineeName,
                'mobile'   => $nomineeMobile,
                'relation' => $nomineeRelation,
                'share'    => $nomineeShare,
            ];
        }

        if (!$nominees) {
            throw new UserError('At least one nominee is required.');
        }
        if (abs($totalShare - 100) > 0.001) {
            throw new UserError("Total nominee share must be 100% (got $totalShare%).");
        }

        // references (optional)
        $references = [];
        $refNames   = $_POST['reference_name'] ?? [];
        if (!is_array($refNames)) {
            $refNames = [];
        }
        foreach ($refNames as $idx => $name) {
            $refName = trim((string) $name);
            if ($refName === '') {
                continue;
            }
            $references[] = [
                'name'        => $refName,
                'designation' => trim((string) ($_POST['reference_designation'][$idx] ?? '')),
            ];
        }

        $encodedNominees   = json_encode($nominees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $encodedReferences = json_encode($references, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        db_run(
            $conn,
            'UPDATE members_list SET nominees = ?, member_references = ? WHERE id = ?',
            'ssi',
            [$encodedNominees, $encodedReferences, $member_db_id]
        );
    }

    // =====================================================
    // STEP 5 :: Documents
    // =====================================================
    $savedDocs = null;
    if ($stepNo === 5) {
        // svg is NOT allowed: an svg can contain scripts (stored XSS)
        $allowedExt = [
            'image/jpeg'          => 'jpg',
            'image/jpg'           => 'jpg',
            'image/png'           => 'png',
            'image/gif'           => 'gif',
            'image/webp'          => 'webp',
            'image/avif'          => 'avif',
            'image/heic'          => 'heic',
            'image/heif'          => 'heif',
            'image/bmp'           => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/tiff'          => 'tiff',
            'image/x-icon'        => 'ico',
        ];

        $maxFileSize = 5 * 1024 * 1024;

        // the path saved in the database is relative to the /server folder (same as before)
        $relDir = "uploads/members/$member_db_id/";
        $absDir = dirname(__DIR__) . "/" . $relDir;
        if (!is_dir($absDir) && !mkdir($absDir, 0775, true)) {
            throw new Exception("Could not create upload folder: $absDir");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $docs  = [];

        $docTypes = $_POST['member_doc_type'] ?? [];
        if (!is_array($docTypes)) {
            $docTypes = [];
        }

        foreach ($docTypes as $idx => $type) {
            $docType        = trim((string) $type);
            $docDescription = trim((string) ($_POST['member_doc_desc'][$idx] ?? ''));
            if ($docType === '') {
                throw new UserError('Document type is required.');
            }

            // file already uploaded earlier? it must live inside THIS member's folder
            $existingPath = trim((string) ($_POST['existing_path'][$idx] ?? ''));
            if ($existingPath !== '' && (strpos($existingPath, $relDir) !== 0 || strpos($existingPath, '..') !== false)) {
                $existingPath = '';
            }

            $fileErr = $_FILES['member_doc']['error'][$idx] ?? UPLOAD_ERR_NO_FILE;
            if ($fileErr === UPLOAD_ERR_OK) {
                $tempFile = $_FILES['member_doc']['tmp_name'][$idx];

                if ($_FILES['member_doc']['size'][$idx] > $maxFileSize) {
                    throw new UserError('File too large (max 5 MB).');
                }

                $fileMIME = $finfo->file($tempFile);
                if (!isset($allowedExt[$fileMIME])) {
                    throw new UserError('Invalid file format.');
                }

                // safe + unique name (the old name had spaces and two files of one type overwrote each other)
                $safeType = trim(preg_replace('/[^A-Za-z0-9]+/', '_', $docType), '_');
                $fileName = $safeType . '_' . $member_db_id . '_' . bin2hex(random_bytes(4)) . '.' . $allowedExt[$fileMIME];

                if (!move_uploaded_file($tempFile, $absDir . $fileName)) {
                    throw new Exception('Could not store the uploaded file.');
                }

                // the row got a new file: remove the old one
                if ($existingPath !== '' && is_file(__DIR__ . '/' . $existingPath)) {
                    @unlink(__DIR__ . '/' . $existingPath);
                }
                $existingPath = $relDir . $fileName;
            } elseif ($fileErr !== UPLOAD_ERR_NO_FILE) {
                throw new UserError('File upload failed (code ' . $fileErr . ').');
            }

            if ($existingPath === '') {
                throw new UserError('Please choose a file for document row ' . ($idx + 1) . '.');
            }

            $docs[] = [
                'type'        => $docType,
                'description' => $docDescription,
                'file'        => $fileName,
                'path'       => $existingPath,
            ];
        }

        if (!$docs) {
            throw new UserError('At least one document is required.');
        }

        $encodedDocs = json_encode($docs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // the old code prepared this query but never executed it
        db_run($conn, 'UPDATE members_list SET documents = ? WHERE id = ?', 'si', [$encodedDocs, $member_db_id]);
        $savedDocs = $docs;
    }

    // =====================================================
    // remember the furthest step that was saved
    // COALESCE: GREATEST(NULL, 1) is NULL in MySQL
    // =====================================================
    db_run(
        $conn,
        'UPDATE members_list SET current_step = GREATEST(COALESCE(current_step, 0), ?) WHERE id = ?',
        'ii',
        [$stepNo, $member_db_id]
    );

    $response = ['success' => true, 'original_member_id' => $member_db_id];
    if ($savedDocs !== null) {
        $response['documents'] = $savedDocs;
    }
    respond($response);
} catch (UserError $err) {
    if ($inTransaction && $conn instanceof mysqli) {
        $conn->rollback();
    }
    respond(['success' => false, 'message' => $err->getMessage()], 422);
} catch (Throwable $err) {
    if ($inTransaction && $conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $ignore) {
        }
    }

    error_log('[insert_members.php] ' . $err->getMessage() . ' @ ' . $err->getFile() . ':' . $err->getLine());

    $payload = ['success' => false, 'message' => 'Server error. Please try again.'];
    if ($DEBUG) {
        $payload['debug'] = $err->getMessage() . ' @ line ' . $err->getLine();
    }
    respond($payload, 500);
}
