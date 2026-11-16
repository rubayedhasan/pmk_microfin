<?php
// connect to the database 
require_once("../includes/config/db.php");

// QUERY:: FETCH COUNTRIES DATA 
$countries_query = $conn->prepare("SELECT country_name, numeric_code FROM countries");
$countries_query->execute();
$countries = $countries_query->get_result()->fetch_all(MYSQLI_ASSOC);

// QUERY:: FETCH EDUCATION CATEGORIES DATA 
$edu_cat_query = $conn->prepare("SELECT * FROM education_categories");
$edu_cat_query->execute();
$edu_cat = $edu_cat_query->get_result()->fetch_all(MYSQLI_ASSOC);

// QUERY:: FETCH EDUCATION List DATA 
$edu_list_query = $conn->prepare("SELECT * FROM education_list");
$edu_list_query->execute();
$edu_list = $edu_list_query->get_result()->fetch_all(MYSQLI_ASSOC);

// QUERY:: FETCH DIVISION DATA
$division_query = $conn->prepare("SELECT * FROM area_division");
$division_query->execute();
$division = $division_query->get_result()->fetch_all(MYSQLI_ASSOC);

// QUERY:: FETCH DISTRICT DATA
$district_query = $conn->prepare("SELECT * FROM area_district");
$district_query->execute();
$district = $district_query->get_result()->fetch_all(MYSQLI_ASSOC);

// QUERY:: FETCH UPAZILLA DATA
$upazilla_query = $conn->prepare("SELECT * FROM area_upazilla");
$upazilla_query->execute();
$upazilla = $upazilla_query->get_result()->fetch_all(MYSQLI_ASSOC);

// QUERY:: FETCH POST OFFICE DATA
$post_office_query = $conn->prepare("SELECT * FROM area_postoffice");
$post_office_query->execute();
$post_office = $post_office_query->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member</title>
    <!-- Linked fontawesome stylesheet  -->
    <link rel="stylesheet" href="../utility_library/fontawesome-5.15.4/css/all.css">
    <!-- Linked bootstrap stylesheet  -->
    <link rel="stylesheet" href="../utility_library/bootstrap-5.3.8/css/bootstrap.css">

    <!-- Linked select2 stylesheet  -->
    <!-- <link rel="stylesheet" href="../utility_library/select2-4.1.0/dist/css/select2.min.css"> -->

    <!-- Linked custom stylesheet  -->
    <link rel="stylesheet" href="../form/css/single_form.css">
    <link rel="stylesheet" href="../table/css/table.css">
    <link rel="stylesheet" href="./styles/member_admission_spinner.css">
    <link rel="stylesheet" href="../custom_styles/select2_custom_style.css">
    <link rel="stylesheet" href="./styles/member_admission.css">
</head>

<body>
    <!-- breadcrumb  -->
    <div class="page-breadcrumb">
        <!-- breadcrumb item  -->
        <div class="page-breadcrumb-item">
            <a href="javascript:void(0)">Members</a> / Member Admission
        </div>

        <!-- back button  -->
        <a class="go-back" href="javascript:void(0)" title="Go Back To Previous Page" onclick="window.history.back()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-back-up">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M9 14l-4 -4l4 -4" />
                <path d="M5 10h11a4 4 0 1 1 0 8h-1" />
            </svg>
        </a>
    </div>


    <!-- ===========================================
                CONTAINER HEADER (STEP-NAV)
        ============================================= -->
    <div class="step-nav" id="stepNav">
        <div class="step-btn" data-step="1">
            <span class="step-badge">
                <i class="fas fa-user"></i>
            </span>
            Member Info
        </div>
        <div class="step-btn" data-step="2">
            <span class="step-badge">
                <i class="fas fa-map-marker-alt"></i>
            </span>
            Address & Contact
        </div>
        <div class="step-btn" data-step="3">
            <span class="step-badge">
                <i class="fas fa-users"></i>
            </span>
            Nominee & References
        </div>
        <div class="step-btn" data-step="4">
            <span class="step-badge">
                <i class="fas fa-info-circle"></i>
            </span>
            Additional Info
        </div>
        <div class="step-btn" data-step="5">
            <span class="step-badge">
                <i class="fas fa-folder-open"></i>
            </span>
            Documents
        </div>
    </div>

    <!-- ===========================================
                        CONTAINER BODY
    ============================================= -->

    <section class="member-admission-container">
        <!-- ========  STEP-1::  Basic Info ======== -->
        <div id="step-1">
            <form id="form-step-1" class="step-form" data-step="1" autocomplete="off">
                <div class="branch-form-wrap">
                    <div class="form-grid">

                        <!-- ======================== INPUTS:: Personal Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                <path d="M9 10a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
                            </svg>
                            Individual Information
                        </div>

                        <!-- FIELD:: member admission date-->
                        <div class="field-group">
                            <label for="admissionDate">
                                Admission Date
                                <span class="req">*</span>
                            </label>
                            <input type="date" name="member_admission_date" id="admissionDate" readonly required>
                        </div>

                        <!-- FIELD:: samity name-->
                        <div class="field-group ">
                            <label for="memberSamity">
                                Samity name
                                <span class="req">*</span>
                            </label>
                            <select name="member_samity_name" id="memberSamity" required>
                                <option value=""> -- Select Samity --</option>
                                <option value="SM001">SM001-Samity 1</option>
                                <option value="SM002">SM002-Samity 2</option>
                                <option value="SM003">SM003-Samity 3</option>
                            </select>
                        </div>

                        <!-- FIELD:: primary product-->
                        <div class="field-group ">
                            <label for="primaryProduct">
                                Primary Product
                                <span class="req">*</span>
                            </label>
                            <select name="member_primary_product" id="primaryProduct" required>
                                <option value="">-- Select Primary Product --</option>
                                <option value="P001">P001-Product 1</option>
                                <option value="P002">P002-Product 2</option>
                                <option value="P003">P003-Product 3</option>
                            </select>
                        </div>

                        <!-- FIELD:: member name-->
                        <div class="field-group">
                            <label for="memberName">
                                Member name
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_name" id="memberName" placeholder="Enter Member Name" required>
                            <span class="nameHints"></span>
                        </div>

                        <!-- FIELD:: member surname-->
                        <div class="field-group">
                            <label for="memberSurname">
                                Member Surname
                            </label>
                            <input type="text" name="member_surname" id="memberSurname" placeholder="Enter Member Surname">
                            <span class="nameHints"></span>
                        </div>

                        <!-- FIELD:: member code-->
                        <div class="field-group">
                            <label for="memberCode">
                                Member Code (Auto)
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_code" id="memberCode" placeholder="Member Code" required>

                            <!-- original member code (for tracking)-->
                            <input type="hidden" name="original_member_code" id="orgMemCode">
                        </div>

                        <!-- FIELD:: father's name-->
                        <div class="field-group">
                            <label for="memberFather">
                                Father's name
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_father" id="memberFather" placeholder="Enter Member Father's Name" required>
                            <span class="nameHints"></span>
                        </div>

                        <!-- FIELD:: mother's name-->
                        <div class="field-group">
                            <label for="memberMother">
                                mother's name
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_mother" id="memberMother" placeholder="Enter Member Mother's Name" required>
                            <span class="nameHints"></span>
                        </div>

                        <!-- FIELD:: nationality-->
                        <div class="field-group ">
                            <label for="memberNationality">
                                Nationality
                                <span class="req">*</span>
                            </label>

                            <select name="member_nationality" id="memberNationality" required>
                                <option value="">-- Select Country --</option>
                                <?php
                                foreach ($countries as $country) {
                                    $selBd = $country['country_name'] === 'Bangladesh' ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($country['numeric_code']); ?>"
                                        <?= $selBd; ?>>
                                        <?= htmlspecialchars($country['numeric_code'] . ' - ' . $country['country_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- FIELD:: member Gender-->
                        <div class="field-group ">
                            <label for="memberGender">
                                Gender
                                <span class="req">*</span>
                            </label>
                            <select name="member_gender" id="memberGender" required>
                                <option value=""> --Select Gender --</option>
                                <option value="Male">Male</option>
                                <option value="Female" selected>Female</option>
                                <option value="Third Gender">Third Gender</option>
                            </select>
                        </div>

                        <!-- FIELD:: member date of Birth-->
                        <div class="field-group">
                            <label for="memberDOB">
                                Date Of Birth
                                <span class="req">*</span>
                            </label>
                            <input type="date" name="member_dob" id="memberDOB" required>
                        </div>

                        <!-- FIELD:: member age-->
                        <div class="field-group">
                            <label for="memberAge">
                                Age
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_age" id="memberAge" placeholder="Age" readonly required>
                        </div>

                        <!-- FIELD:: member marital status-->
                        <div class="field-group ">
                            <label for="memberMaritalStatus">
                                marital Status
                                <span class="req">*</span>
                            </label>
                            <select name="member_marital_status" id="memberMaritalStatus" required>
                                <option value="">-- Select Marital Status --</option>
                                <option value="Unmarried">Unmarried</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widow">Widow</option>
                            </select>
                        </div>

                        <!-- FIELD:: member spouse Name-->
                        <div class="field-group" id="spouseParentField">
                            <label for="memberSpouseName">
                                spouse Name
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_spouse_name" id="memberSpouseName" placeholder="Spouse Name">
                            <span class="nameHints"></span>
                        </div>

                        <!-- FIELD:: member education-->
                        <div class="field-group ">
                            <label for="memberEducation">
                                Education
                                <span class="req">*</span>
                            </label>
                            <select name="member_education" id="memberEducation" required>
                                <option value="">-- Select Degree --</option>
                                <?php
                                foreach ($edu_cat as $cat) {
                                    $hasEducation = false;

                                    foreach ($edu_list as $edu) {
                                        if ($cat['id'] == $edu['category_id']) {

                                            if (!$hasEducation) {
                                                echo '<optgroup label="' .
                                                    htmlspecialchars($cat['category_name']) . '">';

                                                $hasEducation = true;
                                            }
                                ?>

                                            <option value="<?php echo htmlspecialchars($edu['id']); ?>">
                                                <?php echo htmlspecialchars($edu['education_name']); ?>
                                            </option>

                                <?php
                                        }
                                    }

                                    if ($hasEducation) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member mobile-->
                        <div class="field-group">
                            <label for="memberMobile">
                                Mobile
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_mobile" id="memberMobile" placeholder="e.g: 017XXXXXXXX" required>
                            <span id="mobileHints"></span>
                        </div>

                        <!-- FIELD:: admission fee-->
                        <div class="field-group">
                            <label for="memberAdFee">
                                Admission Fee
                                <span class="req">*</span>
                            </label>
                            <input type="number" name="member_admission_fee" id="memberAdFee" value="10" readonly required>
                        </div>

                        <!-- empty cell: for maintain the grid layout  -->
                        <div></div>


                        <!-- ======================== INPUTS:: IDENTITY DATA =================-->

                        <!-- SECTION LABEL  -->
                        <div class="section-label" style="margin-top: 16px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-id" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 7a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3l0 -10" />
                                <path d="M7 10a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M15 8l2 0" />
                                <path d="M15 12l2 0" />
                                <path d="M7 16l10 0" />
                            </svg>
                            Identification Information
                        </div>

                        <!-- FIELD:: smart id-->
                        <div class="field-group">
                            <label for="memberSmartId">
                                SMART ID
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_smart_id" id="memberSmartId" placeholder="10 Digit Smart ID Number" required>
                            <span id="sidHints"></span>
                        </div>

                        <!-- FIELD:: national ID-->
                        <div class="field-group">
                            <label for="memberNID">
                                national ID
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_nid" id="memberNID" placeholder="17 Digit National ID Number" required>
                            <span id="nidHints"></span>
                        </div>

                        <!-- FIELD:: birth registration-->
                        <div class="field-group">
                            <label for="memberBirthReg">
                                birth registration
                            </label>
                            <input type="text" name="member_birth_reg" id="memberBirthReg" placeholder="17 Digit Birth Registration Number">
                        </div>

                        <!-- FIELD:: tin number-->
                        <div class="field-group">
                            <label for="memberTinNo">
                                Tin No
                            </label>
                            <input type="text" name="member_tin_no" id="memberTinNo" placeholder="12 Digit Tin Number">
                        </div>

                        <!-- FIELD:: passport number-->
                        <div class="field-group">
                            <label for="memberPassport">
                                Passport No
                            </label>
                            <input type="text" name="member_passport" id="memberPassport" placeholder="12 Digit Tin Number">
                        </div>

                        <!-- FIELD:: passport Expiry-->
                        <div class="field-group">
                            <label for="memberPassExp">
                                Expiry Date (Passport)
                            </label>
                            <input type="date" name="member_pass_expiry" id="memberPassExp" onfocus="this.showPicker()">
                        </div>

                        <!-- FIELD:: driving license number-->
                        <div class="field-group">
                            <label for="memberDrivingLic">
                                driving license
                            </label>
                            <input type="text" name="member_driving_lic" id="memberDrivingLic" placeholder="12 Digit Tin Number">
                        </div>

                        <!-- FIELD:: driving license Expiry-->
                        <div class="field-group">
                            <label for="memberDriveExp">
                                Expiry Date (driving license)
                            </label>
                            <input type="date" name="member_drive_expiry" id="memberDriveExp" onfocus="this.showPicker()">
                        </div>


                        <!-- ======================== INPUTS:: register Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label" style="margin-top: 16px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-text" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                                <path d="M9 9l1 0" />
                                <path d="M9 13l6 0" />
                                <path d="M9 17l6 0" />
                            </svg>
                            Register Information
                        </div>

                        <!-- FIELD:: admission no-->
                        <div class="field-group">
                            <label for="memberAdNo">
                                Admission No (Manual Form)
                            </label>
                            <input type="text" name="member_admission_no" id="memberAdNo" placeholder="Admission Form's Serial Number">
                        </div>

                        <!-- FIELD:: application no-->
                        <div class="field-group">
                            <label for="memberAppliNo">
                                Application No (Manual Register)
                            </label>
                            <input type="text" name="member_application_no" id="memberAppliNo" placeholder="Admission Register's Serial Number">
                        </div>

                        <!-- empty cell:: maintain the grid layout  -->
                        <din class="span-2"></din>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========  STEP-2::  Address & Contact ======== -->
        <div id="step-2">
            <form id="form-step-2" class="step-form" data-step="2" autocomplete="off">
                <div class="branch-form-wrap">
                    <div class="form-grid">

                        <!-- ======================== INPUTS:: Address As Per NID Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-address-book" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M20 6v12a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2" />
                                <path d="M10 16h6" />
                                <path d="M11 11a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M4 8h3" />
                                <path d="M4 12h3" />
                                <path d="M4 16h3" />
                            </svg>
                            Address According to NID
                        </div>

                        <!-- FIELD:: member NID division-->
                        <div class="field-group ">
                            <label for="MemberNIDDivision">
                                Division
                                <span class="req">*</span>
                            </label>
                            <select name="member_NID_division" id="MemberNIDDivision" required>
                                <option value="">-- Select Division --</option>
                                <?php
                                foreach ($division as $div) { ?>
                                    <option value="<?php echo htmlspecialchars($div['division_code']); ?>">
                                        <?php echo htmlspecialchars($div['division_name']); ?>
                                    </option>
                                <?php }

                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member NID District-->
                        <div class="field-group ">
                            <label for="MemberNIDDistrict">
                                District
                                <span class="req">*</span>
                            </label>
                            <select name="member_NID_district" id="MemberNIDDistrict" required>
                                <option value="">-- Select District --</option>
                                <?php
                                foreach ($division as $div) {
                                    $hasDiv = false;
                                    foreach ($district as $dist) {
                                        if ($div['division_code'] === $dist['division_code']) {
                                            if (!$hasDiv) {
                                                echo "<optgroup label='$div[division_name]'>";
                                                $hasDiv = true;
                                            }
                                ?>
                                            <option value="<?php echo htmlspecialchars($dist['district_code']); ?>">
                                                <?php echo htmlspecialchars($dist['disctrict_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                    if ($hasDiv) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member NID Upazila-->
                        <div class="field-group ">
                            <label for="MemberNIDUpazila">
                                Thana / Upazila
                                <span class="req">*</span>
                            </label>
                            <select name="member_NID_Upazila" id="MemberNIDUpazila" required>
                                <option value="">-- Select Upazila --</option>
                                <?php
                                foreach ($district as $dist) {
                                    $hasDist = false;
                                    foreach ($upazilla as $upa) {
                                        if ($dist['district_code'] === $upa['district_code']) {
                                            if (!$hasDist) {
                                                echo "<optgroup label='$dist[disctrict_name]'>";
                                                $hasDist = true;
                                            }
                                ?>
                                            <option value="<?php echo htmlspecialchars($upa['upazilla_code']); ?>">
                                                <?php echo htmlspecialchars($upa['upazilla_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                    if ($hasDist) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member NID post Office-->
                        <div class="field-group ">
                            <label for="MemberNIDPostOffice">
                                Post Office
                                <span class="req">*</span>
                            </label>
                            <select name="member_NID_post_office" id="MemberNIDPostOffice" required>
                                <option value="">-- Select Post Office --</option>
                                <?php
                                foreach ($post_office as $post) { ?>
                                    <option value="<?php echo htmlspecialchars($post['post_code']); ?>">
                                        <?php echo htmlspecialchars($post['post_office_name']); ?>
                                    </option>
                                <?php }

                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member NID union-->
                        <div class="field-group">
                            <label for="MemberNIDUnion">
                                Pouroshova/Union
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_NID_union" id="MemberNIDUnion" placeholder="Union Name" required>
                        </div>

                        <!-- FIELD:: member NID village-->
                        <div class="field-group">
                            <label for="memberNIDVillage">
                                Village
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_NID_village" id="memberNIDVillage" placeholder="Village Name" required>
                        </div>

                        <!-- FIELD:: member NID street-->
                        <div class="field-group">
                            <label for="memberNIDHouseNo">
                                House / Street No (NID)
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_NID_house" id="memberNIDHouseNo" placeholder="House/Street Address" required>
                        </div>


                        <!-- ======================== INPUTS:: Present Address Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label flex-section-label">
                            <div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-pin" style="margin-bottom: 2px;">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h2.5" />
                                    <path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879" />
                                    <path d="M19 18v.01" />
                                </svg>
                                Present Address
                            </div>

                            <!-- same as nid checkbox  -->
                            <div class="field-group span-4" style="flex-direction: row;">
                                <input type="checkbox" id="presentAsNid">
                                <label for="presentAsNid">Same As NID</label>
                            </div>
                        </div>

                        <!-- FIELD:: member present division-->
                        <div class="field-group ">
                            <label for="MemberPresentDivision">
                                Division
                                <span class="req">*</span>
                            </label>
                            <select name="member_present_division" id="MemberPresentDivision" required>
                                <option value="">-- Select Division --</option>
                                <?php
                                foreach ($division as $div) { ?>
                                    <option value="<?php echo htmlspecialchars($div['division_code']); ?>">
                                        <?php echo htmlspecialchars($div['division_name']); ?>
                                    </option>
                                <?php }

                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member present District-->
                        <div class="field-group ">
                            <label for="MemberPresentDistrict">
                                District
                                <span class="req">*</span>
                            </label>
                            <select name="member_present_district" id="MemberPresentDistrict" required>
                                <option value="">-- Select District --</option>
                                <?php
                                foreach ($division as $div) {
                                    $hasDiv = false;
                                    foreach ($district as $dist) {
                                        if ($div['division_code'] === $dist['division_code']) {
                                            if (!$hasDiv) {
                                                echo "<optgroup label='$div[division_name]'>";
                                                $hasDiv = true;
                                            }
                                ?>
                                            <option value="<?php echo htmlspecialchars($dist['district_code']); ?>">
                                                <?php echo htmlspecialchars($dist['disctrict_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                    if ($hasDiv) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member present Upazila-->
                        <div class="field-group ">
                            <label for="MemberPresentUpazila">
                                Thana / Upazila
                                <span class="req">*</span>
                            </label>
                            <select name="member_present_Upazila" id="MemberPresentUpazila" required>
                                <option value="">-- Select Upazila --</option>
                                <?php
                                foreach ($district as $dist) {
                                    $hasDist = false;
                                    foreach ($upazilla as $upa) {
                                        if ($dist['district_code'] === $upa['district_code']) {
                                            if (!$hasDist) {
                                                echo "<optgroup label='$dist[disctrict_name]'>";
                                                $hasDist = true;
                                            }
                                ?>
                                            <option value="<?php echo htmlspecialchars($upa['upazilla_code']); ?>">
                                                <?php echo htmlspecialchars($upa['upazilla_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                    if ($hasDist) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member present Post Office-->
                        <div class="field-group ">
                            <label for="MemberPresentPostOffice">
                                Post Office
                                <span class="req">*</span>
                            </label>
                            <select name="member_present_post_office" id="MemberPresentPostOffice" required>
                                <option value="">-- Select Post Office --</option>
                                <?php
                                foreach ($post_office as $post) { ?>
                                    <option value="<?php echo htmlspecialchars($post['post_code']); ?>">
                                        <?php echo htmlspecialchars($post['post_office_name']); ?>
                                    </option>
                                <?php }

                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member present union-->
                        <div class="field-group">
                            <label for="MemberPresentUnion">
                                Pouroshova/Union
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_present_union" id="MemberPresentUnion" placeholder="Union Name" required>
                        </div>

                        <!-- FIELD:: member present village-->
                        <div class="field-group">
                            <label for="memberPresentVillage">
                                Village
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_present_village" id="memberPresentVillage" placeholder="Village Name" required>
                        </div>

                        <!-- FIELD:: member present street-->
                        <div class="field-group">
                            <label for="memberPresentHouseNo">
                                House / Street No (Present)
                            </label>
                            <input type="text" name="member_present_house" id="memberPresentHouseNo" placeholder="House/Street Address">
                        </div>

                        <!-- ======================== INPUTS:: Permanent Address Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label flex-section-label">
                            <div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin" style="margin-bottom: 2px;">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                    <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0" />
                                </svg>
                                Permanent Address
                            </div>

                            <!-- same as nid checkbox  -->
                            <div class="field-group span-4" style="flex-direction: row;">
                                <input type="checkbox" id="permanentAsNid">
                                <label for="permanentAsNid">Same As NID</label>
                            </div>
                        </div>

                        <!-- FIELD:: member Permanent division-->
                        <div class="field-group ">
                            <label for="MemberPermanentDivision">
                                Division
                                <span class="req">*</span>
                            </label>
                            <select name="member_permanent_division" id="MemberPermanentDivision" required>
                                <option value="">-- Select Division --</option>
                                <?php
                                foreach ($division as $div) { ?>
                                    <option value="<?php echo htmlspecialchars($div['division_code']); ?>">
                                        <?php echo htmlspecialchars($div['division_name']); ?>
                                    </option>
                                <?php }

                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member Permanent District-->
                        <div class="field-group ">
                            <label for="MemberPermanentDistrict">
                                District
                                <span class="req">*</span>
                            </label>
                            <select name="member_permanent_district" id="MemberPermanentDistrict" required>
                                <option value="">-- Select District --</option>
                                <?php
                                foreach ($division as $div) {
                                    $hasDiv = false;
                                    foreach ($district as $dist) {
                                        if ($div['division_code'] === $dist['division_code']) {
                                            if (!$hasDiv) {
                                                echo "<optgroup label='$div[division_name]'>";
                                                $hasDiv = true;
                                            }
                                ?>
                                            <option value="<?php echo htmlspecialchars($dist['district_code']); ?>">
                                                <?php echo htmlspecialchars($dist['disctrict_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                    if ($hasDiv) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member Permanent Upazila-->
                        <div class="field-group ">
                            <label for="MemberPermanentUpazila">
                                Thana / Upazila
                                <span class="req">*</span>
                            </label>
                            <select name="member_permanent_Upazila" id="MemberPermanentUpazila" required>
                                <option value="">-- Select Upazila --</option>
                                <?php
                                foreach ($district as $dist) {
                                    $hasDist = false;
                                    foreach ($upazilla as $upa) {
                                        if ($dist['district_code'] === $upa['district_code']) {
                                            if (!$hasDist) {
                                                echo "<optgroup label='$dist[disctrict_name]'>";
                                                $hasDist = true;
                                            }
                                ?>
                                            <option value="<?php echo htmlspecialchars($upa['upazilla_code']); ?>">
                                                <?php echo htmlspecialchars($upa['upazilla_name']); ?>
                                            </option>
                                <?php
                                        }
                                    }
                                    if ($hasDist) {
                                        echo '</optgroup>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member Permanent Post Office-->
                        <div class="field-group ">
                            <label for="MemberPermanentPostOffice">
                                Post Office
                                <span class="req">*</span>
                            </label>
                            <select name="member_permanent_post_office" id="MemberPermanentPostOffice" required>
                                <option value="">-- Select Post Office --</option>
                                <?php
                                foreach ($post_office as $post) { ?>
                                    <option value="<?php echo htmlspecialchars($post['post_code']); ?>">
                                        <?php echo htmlspecialchars($post['post_office_name']); ?>
                                    </option>
                                <?php }

                                ?>
                            </select>
                        </div>

                        <!-- FIELD:: member Permanent union-->
                        <div class="field-group">
                            <label for="MemberPermanentUnion">
                                Pouroshova/Union
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_permanent_union" id="MemberPermanentUnion" placeholder="Union Name" required>
                        </div>

                        <!-- FIELD:: member Permanent village-->
                        <div class="field-group">
                            <label for="memberPermanentVillage">
                                Village
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_permanent_village" id="memberPermanentVillage" placeholder="Village Name" required>
                        </div>

                        <!-- FIELD:: member Permanent street-->
                        <div class="field-group">
                            <label for="memberPermanentHouseNo">
                                House / Street No (Permanent)
                            </label>
                            <input type="text" name="member_permanent_house" id="memberPermanentHouseNo" placeholder="House/Street Address">
                        </div>

                        <!-- ======================== INPUTS:: Permanent Address Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-phone" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                            </svg>
                            Contact Number
                        </div>

                        <!-- FIELD:: member family contact-->
                        <div class="field-group">
                            <label for="memberFamilyContact">
                                Contact (family Member)
                            </label>
                            <input type="text" name="member_family_contact" id="memberFamilyContact" placeholder="Spouse's or Family Member's Contact Number">
                        </div>

                        <!-- FIELD:: member permanent contact-->
                        <div class="field-group">
                            <label for="memberPermanentContact">
                                Permanent Contact
                                <span class="req">*</span>
                            </label>
                            <input type="text" name="member_permanent_contact" id="memberPermanentContact" placeholder="Permanent Contact Number" require>
                            <span id="mobileHintsPerCon"></span>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <!-- ========  STEP-3::  Nominee & References ======== -->
        <div id="step-3">
            <form id="form-step-3" class="step-form" data-step="3" autocomplete="off">
                <div class="branch-form-wrap">
                    <div class="form-grid">

                        <!-- ======================== INPUTS:: Nominee Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label flex-section-label">
                            <div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-replace-user" style="margin-bottom: 2px;">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M21 11v-3c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-6m0 0l3 3m-3 -3l3 -3" />
                                    <path d="M3 13.013v3c0 .53 .211 1.039 .586 1.414c.375 .375 .884 .586 1.414 .586h6m0 0l-3 -3m3 3l-3 3" />
                                    <path d="M16 16.502c0 .53 .211 1.039 .586 1.414c.375 .375 .884 .586 1.414 .586c.53 0 1.039 -.211 1.414 -.586c.375 -.375 .586 -.884 .586 -1.414c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414" />
                                    <path d="M4 4.502c0 .53 .211 1.039 .586 1.414c.375 .375 .884 .586 1.414 .586c.53 0 1.039 -.211 1.414 -.586c.375 -.375 .586 -.884 .586 -1.414c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414" />
                                    <path d="M21 21.499c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-2c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414" />
                                    <path d="M9 9.499c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586h-2c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414" />
                                </svg>
                                Nominee Information
                            </div>

                            <button type="button" id="add-nominee">
                                <i class="fas fa-plus-circle"></i>
                                Add Nominee
                            </button>
                        </div>

                        <!-- nominee container  -->
                        <div class="table-card span-4">

                            <div class="table-overlay" aria-live="polite"></div>

                            <div class="table-responsive">
                                <table class="employee-table">
                                    <thead>
                                        <tr>
                                            <th>Nominee Name</th>
                                            <th>Mobile (Nominee)</th>
                                            <th>Relation With Nominee</th>
                                            <th>Share(%)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="nominee-table-body"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- dynamic row  -->
                        <template id="nominee-row-template">
                            <tr>
                                <!-- FIELD:: Nominee name  -->
                                <td>
                                    <div class="field-group">
                                        <input type="text" name="nominee_name[]" placeholder="Enter Nominee Name" required>
                                        <span class="nameHints"></span>
                                    </div>
                                </td>

                                <!-- FIELD:: Nominee Mobile  -->
                                <td>
                                    <div class="field-group">
                                        <input type="text" name="nominee_mobile[]" class="" placeholder="Enter Mobile Number of Nominee" required>
                                        <span class="nominee-mobile-hints"></span>
                                    </div>
                                </td>

                                <!-- FIELD:: Nominee relation  -->
                                <td>
                                    <div class="field-group">
                                        <input type="text" name="nominee_relation[]" class="" placeholder="Enter relation with Nominee" required>
                                    </div>
                                </td>

                                <!-- FIELD:: Nominee share  -->
                                <td>
                                    <div class="field-group">
                                        <input type="number" name="nominee_share[]" min="0" max="100" value="100" required>
                                    </div>
                                </td>

                                <!-- FIELD:: Nominee action  -->
                                <td style="vertical-align: middle; text-align: center;">
                                    <button type="button" class="act-btn act-delete nominee-remove-btn">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                    </div>
                </div>

                <div class="branch-form-wrap" style="margin-top: 32px;">
                    <div class="form-grid">
                        <!-- ======================== INPUTS:: Reference Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label flex-section-label">
                            <div>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-check" style="margin-bottom: 2px;">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />
                                    <path d="M15 19l2 2l4 -4" />
                                </svg>
                                Reference Information
                            </div>

                            <button type="button" id="add-reference">
                                <i class="fas fa-plus-circle"></i>
                                Add Reference
                            </button>
                        </div>

                        <!-- nominee container  -->
                        <div class="table-card span-4">

                            <div class="table-overlay" aria-live="polite"></div>

                            <div class="table-responsive">
                                <table class="employee-table">
                                    <thead>
                                        <tr>
                                            <th>Reference Person Name</th>
                                            <th>Reference Person Designation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reference-table-body"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- dynamic row  -->
                        <template id="reference-row-template">
                            <tr>
                                <!-- FIELD:: Reference Person name  -->
                                <td>
                                    <div class="field-group">
                                        <input type="text" name="reference_name[]" class="" placeholder="Enter Reference Person Name">
                                        <span class="nameHints"></span>
                                    </div>
                                </td>

                                <!-- FIELD:: reference person designation  -->
                                <td>
                                    <div class="field-group">
                                        <input type="text" name="reference_designation[]" class="" placeholder="Enter Reference Person Designation">
                                    </div>
                                </td>

                                <!-- FIELD:: Nominee action  -->
                                <td style="vertical-align: middle; text-align: center;">
                                    <button type="button" class="act-btn act-delete reference-remove-btn">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========  STEP-4::  Additional Information ======== -->
        <div id="step-4">
            <form id="form-step-4" class="step-form" data-step="4" autocomplete="off">
                <div class="branch-form-wrap">
                    <div class="form-grid">

                        <!-- SECTION LABEL  -->
                        <div class="section-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-info-hexagon" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M19.875 6.27c.7 .398 1.13 1.143 1.125 1.948v7.284c0 .809 -.443 1.555 -1.158 1.948l-6.75 4.27a2.269 2.269 0 0 1 -2.184 0l-6.75 -4.27a2.225 2.225 0 0 1 -1.158 -1.948v-7.285c0 -.809 .443 -1.554 1.158 -1.947l6.75 -3.98a2.33 2.33 0 0 1 2.25 0l6.75 3.98h-.033" />
                                <path d="M12 9h.01" />
                                <path d="M11 12h1v4h1" />
                            </svg>
                            Additional Information
                        </div>

                        <!-- FIELD:: member type  -->
                        <div class="field-group ">
                            <label for="memberType">
                                Member Type
                                <span class="req">*</span>
                            </label>
                            <select name="member_type" id="memberType" required>
                                <option value="">--Select Member Type --</option>
                                <option value="General">General</option>
                                <option value="Group Leader">Group Leader</option>
                                <option value="Samity Leader">Samity Leader</option>
                                <option value="Samity Secretary">Samity Secretary</option>
                                <option value="President">President</option>
                                <option value="Cashier">Cashier</option>
                                <option value="Executive Member">Executive Member</option>
                            </select>
                        </div>

                        <!-- FIELD:: member profession  -->
                        <div class="field-group ">
                            <label for="memberProfession">
                                Member Profession
                                <span class="req">*</span>
                            </label>
                            <select name="member_profession" id="memberProfession" required>
                                <option value="">--Select Profession --</option>
                                <optgroup label="Household & Domestic Work">
                                    <option value="Housewife / Homemaker">Housewife / Homemaker</option>
                                    <option value="Domestic Worker">Domestic Worker</option>
                                    <option value="Caregiver">Caregiver</option>
                                </optgroup>

                                <optgroup label="Agriculture & Farming">
                                    <option value="Farmer">Farmer</option>
                                    <option value="Agricultural Worker">Agricultural Worker</option>
                                    <option value="Day Labourer">Day Labourer</option>
                                    <option value="Vegetable Grower">Vegetable Grower</option>
                                    <option value="Poultry Farmer">Poultry Farmer</option>
                                    <option value="Livestock Raiser">Livestock Raiser</option>
                                    <option value="Fish Farmer">Fish Farmer</option>
                                </optgroup>

                                <optgroup label="Small Business & Entrepreneurship">
                                    <option value="Small Business Owner">Small Business Owner</option>
                                    <option value="Shopkeeper">Shopkeeper</option>
                                    <option value="Street Vendor">Street Vendor</option>
                                    <option value="Tailor / Sewing Business">Tailor / Sewing Business</option>
                                    <option value="Food Seller">Food Seller</option>
                                    <option value="Home-based Business Owner">Home-based Business Owner</option>
                                    <option value="Handicraft Worker">Handicraft Worker</option>
                                </optgroup>

                                <optgroup label="Garments & Handicrafts">
                                    <option value="Garment Worker">Garment Worker</option>
                                    <option value="Tailor">Tailor</option>
                                    <option value="Sewing Worker">Sewing Worker</option>
                                    <option value="Embroidery Worker">Embroidery Worker</option>
                                    <option value="Jute Products Maker">Jute Products Maker</option>
                                    <option value="Bamboo / Cane Crafts Worker">Bamboo / Cane Crafts Worker</option>
                                </optgroup>

                                <optgroup label="Labour & Transportation">
                                    <option value="Construction Worker">Construction Worker</option>
                                    <option value="Agricultural Labourer">Agricultural Labourer</option>
                                    <option value="Rickshaw Puller">Rickshaw Puller</option>
                                    <option value="Van Driver">Van Driver</option>
                                    <option value="Transport Worker">Transport Worker</option>
                                    <option value="Factory Worker">Factory Worker</option>
                                </optgroup>

                                <optgroup label="Education">
                                    <option value="School Teacher">School Teacher</option>
                                    <option value="Madrasa Teacher">Madrasa Teacher</option>
                                    <option value="Private Tutor">Private Tutor</option>
                                </optgroup>

                                <optgroup label="Service & Employment">
                                    <option value="Private Employee">Private Employee</option>
                                    <option value="NGO Worker">NGO Worker</option>
                                    <option value="Community Health Worker">Community Health Worker</option>
                                    <option value="Government Employee">Government Employee</option>
                                    <option value="Security Guard">Security Guard</option>
                                </optgroup>

                                <optgroup label="Others">
                                    <option value="Other">Other</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- FIELD:: member religion  -->
                        <div class="field-group ">
                            <label for="memberReligion">
                                religion
                                <span class="req">*</span>
                            </label>
                            <select name="member_religion" id="memberReligion" required>
                                <option value="">--Select Religion --</option>
                                <option value="Islam" selected>Islam</option>
                                <option value="Hinduism">Hinduism</option>
                                <option value="Buddhism">Buddhism</option>
                                <option value="Christianity">Christianity</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- FIELD:: member Special Category -->
                        <div class="field-group ">
                            <label for="memberSPCategory">Special Category</label>
                            <select name="member_sp_category" id="memberSPCategory">
                                <option value="">-- Select Category --</option>
                                <option value="Freedom Fighter">Freedom Fighter</option>
                                <option value="Freedom Fighter Family Member">Freedom Fighter's Family Member</option>
                                <option value="Disabled Freedom Fighter">Disabled Freedom Fighter</option>
                                <option value="Person with Disability">Person with Disability</option>
                                <option value="Tribal / Indigenous Community">Tribal / Indigenous Community</option>
                                <option value="Other">Other</option>

                            </select>
                        </div>

                        <!-- FIELD:: member's family member -->
                        <div class="field-group">
                            <label for="memberFamilyMember">Number of Family Member</label>
                            <input type="number" name="member_family_member" id="memberFamilyMember" placeholder="No of Family Member">
                        </div>

                        <!-- FIELD:: member's yearly income -->
                        <div class="field-group">
                            <label for="memberYearlyIncome">Yearly Income</label>
                            <input type="number" name="member_yearly_income" id="memberYearlyIncome" placeholder="Yearly Income">
                        </div>

                        <!-- FIELD:: member's land area -->
                        <div class="field-group">
                            <label for="memberLandArea">Land Area (Acre)</label>
                            <input type="number" name="member_land_area" id="memberLandArea" placeholder="Land Area (Acre)">
                        </div>

                        <!-- FIELD:: member permanent resident -->
                        <div class="field-group ">
                            <label for="memberPermanentResident">
                                permanent resident
                                <span class="req">*</span>
                            </label>
                            <select name="member_permanent_resident" id="memberPermanentResident" required>
                                <option value="">-- Select Resident --</option>
                                <option value="No">No</option>
                                <option value="Yes" selected>Yes</option>
                            </select>
                        </div>

                        <!-- FIELD:: member permanent resident -->
                        <div class="field-group span-2">
                            <label for="memberFixedAsset">
                                fixed Asset Description
                            </label>
                            <textarea name="member_fixed_asset" id="memberFixedAsset" class="textarea-note" placeholder="Fixed Asset Description..."></textarea>
                        </div>
                        <!-- FIELD:: member note -->
                        <div class="field-group span-2">
                            <label for="memberFixedAsset">
                                Note
                            </label>
                            <textarea name="member_note" id="memberNote" class="textarea-note" placeholder="Note..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========  STEP-5::  Documents ======== -->
        <div id="step-5">
            <form id="form-step-5" class="step-form" data-step="5" autocomplete="off">
                <div class="branch-form-wrap">
                    <div class="form-grid">

                        <!-- ======================== INPUTS:: Address As Per NID Data  =================-->
                        <!-- SECTION LABEL  -->
                        <div class="section-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-license" style="margin-bottom: 2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-14a2 2 0 1 1 2 2h-2m2 -4h-11a3 3 0 0 0 -3 3v11" />
                                <path d="M9 7l4 0" />
                                <path d="M9 11l4 0" />
                            </svg>
                            Documents
                        </div>

                        <div class="member-document-container span-4">
                            <!-- document main container where the clone (document-container) append  -->
                            <div id="document-main-container"></div>

                            <!-- dynamic document row template  -->
                            <template id="document-row-template">
                                <div class="document-container">
                                    <!--FIELD::  doc type  -->
                                    <div class="field-group ">
                                        <label for="memberDocType">Document Type
                                            <span class="req">*</span>
                                        </label>
                                        <select name="member_doc_type[]" id="memberDocType" required>
                                            <option value="">-- Select Doc Type --</option>
                                            <option value="Member Picture">Member Picture</option>
                                            <option value="Member Signature">Member Signature</option>
                                            <option value="Member NID">Member NID</option>
                                            <option value="Admission Form">Admission Form</option>
                                            <option value="Loan Proposal">Loan Proposal</option>
                                            <option value="Contact Information">Contact Information</option>
                                            <option value="KYC Form">KYC Form</option>
                                            <option value="Trade License">Trade License</option>
                                            <option value="Nominee NID">Nominee NID</option>
                                            <option value="Nominee Picture">Nominee Picture</option>
                                            <option value="Income Certificate">Income Certificate</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <!--FIELD::  doc type  -->
                                    <div class="field-group">
                                        <label for="memberDocDesc">Description</label>
                                        <textarea name="member_doc_desc[]" id="memberDocDesc" class="textarea-note" placeholder="Description About Document..."></textarea>
                                    </div>

                                    <!--FIELD::  doc  -->
                                    <div class="field-group">
                                        <label for="memberDoc">
                                            Document
                                            <span class="req">*</span>
                                        </label>
                                        <!-- <input type="file" name="member_doc[]" id="memberDoc" accept=".jpg,.jpeg,.png" required> -->
                                        <input type="file" name="member_doc[]" id="memberDoc" accept=".jpg, .jpeg, .png, .webp, .gif, .avif, .svg, .bmp, .tiff, .tif, .heic, .heif, .ico, image/jpeg, image/png, image/webp, image/gif, image/avif, image/svg+xml, image/bmp, image/tiff, image/heic, image/heif, image/x-icon" required>

                                        <!-- path of the file already saved on the server (filled by JS after a successful save) -->
                                        <input type="hidden" name="existing_path[]" value="" />
                                    </div>

                                    <!--FIELD:: doc preview  -->
                                    <div class="field-group">
                                        <label for="memberDoc">
                                            Preview
                                        </label>
                                        <figure class="member-doc-preview">
                                            <img src="../assets/images/image_placeholder.png" alt="doc preview">
                                        </figure>
                                    </div>


                                    <!-- doc row action (remove)  -->
                                    <div class="doc-action">
                                        <button type="button" class="doc-remove-btn act-btn act-delete">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- doc row add btn  -->
                            <div class="add-doc-container">
                                <button type="button" id="add-doc-btn">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <!-- admission action buttons  -->
        <div class="admission-actions">

            <button type="button" class="add-btn-act prev-btn" id="PrevBtn">
                <span>
                    <i class="fas fa-angle-left"></i>
                </span>
                Previous
            </button>

            <button type="button" class="add-btn-act next-btn" id="NextBtn">
                Next
                <span>
                    <i class="fas fa-angle-right"></i>
                </span>
            </button>

            <button type="submit" class="add-btn-act submit-btn" id="submitBtn">
                <span>
                    <i class="far fa-save"></i>
                </span>
                Save & Submit
            </button>

            <button type="button" class="add-btn-act cancel-btn" id="cancelBtn">
                <span>
                    <i class="fas fa-reply"></i>
                </span>
                Cancel
            </button>

        </div>

    </section>


    <!-- LInked jQuery script  -->
    <!-- <script src="../utility_library/JQuery-4.0/jQuery-4.0.js"></script> -->

    <!-- LInked fontawesome script  -->
    <script src="../utility_library/fontawesome-5.15.4/js/all.js"></script>
    <!-- Linked bootstrap script  -->
    <script src="../utility_library/bootstrap-5.3.8/js/bootstrap.js"></script>

    <!-- Linked select2 script  -->
    <!-- <script src="../utility_library/select2-4.1.0/dist/js/select2.min.js"></script> -->

    <!-- Linked custom script  -->
    <script src="./script/member_admission.js"></script>

    <!-- select2 initializer  -->
    <!-- <script>
        $(document).ready(function() {
            $('.custom-select-container select').select2({
                // closeOnSelect: true,
                dropdownCssClass: 'custom-dropdown-container',
                width: '100%'
            });
        });
    </script> -->
</body>

</html>