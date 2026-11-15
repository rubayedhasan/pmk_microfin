<?php
session_start();
include('../../include/db_connect.php');
error_reporting(0);

if (strlen($_SESSION['login']) == 0) {
    header('location:../index.php');
} else {

    $PageID = 'P032';
    $ModulePage = 'P013';
    $ModuleID = 002;
    include('../../module_admin/user_query.php');


    if (!in_array($ModulePage, $RolModule)) {
        header('location:../../include/user_permission_require.php');
    } elseif (!in_array($PageID, $RoleUpdateViw)) {
        header('location:../../include/user_permission_require.php');
    } else {

        // Get employee_id from URL
        $edit_employee_id = isset($_GET['id']) ? mysqli_real_escape_string($conn_hrm, trim($_GET['id'])) : '';

        if (empty($edit_employee_id)) {
            die('Employee ID is required to edit.');
        }

        // Confirm employee exists
        $checkEmp = mysqli_query($conn_hrm, "SELECT emp_gen_id FROM empl_gen WHERE employee_id = '$edit_employee_id' LIMIT 1");
        if (!$checkEmp || mysqli_num_rows($checkEmp) === 0) {
            die("Employee ID '$edit_employee_id' not found.");
        }

        include '../../include/sidebar.php';
        include '../../include/header.php';
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Edit Employee</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
            <link rel="stylesheet" href="../css/multi_step_form.css">
        </head>

        <body>
            <div id="mainContent" class="main-content">
                <div class="form-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="title">
                            <i class="bi bi-person-gear me-2" style="color:var(--brand)"></i>Edit Employee &mdash;
                            <span style="color:var(--brand)"><?php echo htmlspecialchars($edit_employee_id); ?></span>
                        </span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" onclick="handleCancel()">
                                <i class="bi bi-x-lg me-1"></i>Cancel
                            </button>
                            <button class="btn btn-brand btn-sm" onclick="handleSave()">
                                <i class="bi bi-floppy-fill me-1"></i>Save &amp; Close
                            </button>
                        </div>
                    </div>
                    <div class="step-nav" id="stepNav">
                        <button class="step-btn active" data-step="1" onclick="goTo(1)"><span class="step-badge">1</span>
                            Employment</button>
                        <button class="step-btn" data-step="2" onclick="goTo(2)"><span class="step-badge">2</span>
                            Personal</button>
                        <button class="step-btn" data-step="3" onclick="goTo(3)"><span class="step-badge">3</span>
                            Identification</button>
                        <button class="step-btn" data-step="4" onclick="goTo(4)"><span class="step-badge">4</span>
                            Address</button>
                        <button class="step-btn" data-step="5" onclick="goTo(5)"><span class="step-badge">5</span>
                            Education</button>
                        <button class="step-btn" data-step="6" onclick="goTo(6)"><span class="step-badge">6</span> Training
                            Experience</button>
                        <button class="step-btn" data-step="7" onclick="goTo(7)"><span class="step-badge">7</span> Job
                            Experience</button>
                        <button class="step-btn" data-step="8" onclick="goTo(8)"><span class="step-badge">8</span>
                            Guarantor</button>
                        <button class="step-btn" data-step="9" onclick="goTo(9)"><span class="step-badge">9</span>
                            Nominee</button>
                    </div>
                </div>

                <div class="py-4" style="">
                    <div class="form-card">
                        <div class="p-4" id="formLoadingMsg">
                            <p class="text-muted"><i class="bi bi-hourglass-split me-2"></i>Loading employee data...</p>
                        </div>
                        <div class="p-4" id="formContent" style="display:none">

                            <!-- STEP 1: Employment -->
                            <div class="step-panel active" id="step-1">
                                <p class="section-label"><i class="bi bi-briefcase me-2"></i>Step 1 &mdash; Employment
                                    Information</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="employee_id" id="employee_id"
                                            value="<?php echo htmlspecialchars($edit_employee_id); ?>" readonly
                                            style="background:#f8f9fa" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Joining Date</label>
                                        <input type="date" onfocus="this.showPicker()" name="emp_joining_date"
                                            id="emp_joining_date" class="form-control" onchange="calcConfirmDate()" required />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                                        <input type="text" name="employee_name" id="employee_name" class="form-control"
                                            placeholder="Full name" required />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select" name="work_station" id="work_station" required>
                                            <option value="">Select Branch</option>
                                            <?php
                                            $query_branch = mysqli_query($conn_ad, "SELECT `branch_code`,`branch_name` FROM `office_branch` WHERE branch_status = 1 ORDER BY branch_code ASC");
                                            while ($result_branch = mysqli_fetch_assoc($query_branch)) {
                                                echo '<option value="' . $result_branch['branch_code'] . '">' . $result_branch['branch_code'] . '-' . $result_branch['branch_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="">Select Department</option>
                                            <?php
                                            $query_dept = mysqli_query($conn_ad, "SELECT `dep_id`, `dep_name`, `dep_code` FROM `department` ORDER BY `dep_name` ASC");
                                            while ($result_dept = mysqli_fetch_assoc($query_dept)) {
                                                echo '<option value="' . $result_dept['dep_id'] . '">' . $result_dept['dep_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                                        <select class="form-select" id="designation" name="designation" required>
                                            <option value="">Select Designation</option>
                                            <?php
                                            $query_desig = mysqli_query($conn_ad, "SELECT `desig_code`, `desig_name`, `desig_short_name`, `salary_grade` FROM `designation` ORDER BY `desig_name` ASC");
                                            while ($result_desig = mysqli_fetch_assoc($query_desig)) {
                                                echo '<option value="' . $result_desig['desig_code'] . '">' . $result_desig['desig_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Project's Name <span class="text-danger">*</span></label>
                                        <select class="form-select" id="project_name" name="project_name" required>
                                            <option value="">Select Project's Name</option>
                                            <?php
                                            $query_projectName = mysqli_query($conn_ad, "SELECT `program_id`, `program_code`, `program_name`, `program_description` FROM `program_name` ORDER BY `program_name` ASC");
                                            while ($result_projectName = mysqli_fetch_assoc($query_projectName)) {
                                                echo '<option value="' . $result_projectName['program_code'] . '">' . $result_projectName['program_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Employee Type</label>
                                        <select name="emploee_type" id="emploee_type" class="form-select">
                                            <option value="">Select type</option>
                                            <option value="Regular">Regular</option>
                                            <option value="Contractual">Contractual</option>
                                            <option value="Intern">Intern</option>
                                            <option value="Part-time">Part-time</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Amount of Security Money</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="font-size:13px">৳</span>
                                            <input type="number" name="security_money" id="security_money" class="form-control"
                                                placeholder="0.00" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Initial Deposit Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="font-size:13px">৳</span>
                                            <input type="number" name="deposit_money" id="deposit_money" class="form-control"
                                                placeholder="0.00" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Probation Period</label>
                                        <select name="probation_period" id="probation_period" class="form-select"
                                            onchange="calcConfirmDate()">
                                            <option value="">Select type</option>
                                            <option value="3">3 Months</option>
                                            <option value="6">6 Months</option>
                                            <option value="9">9 Months</option>
                                            <option value="12">12 Months</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tentative Confirmation Date</label>
                                        <input type="date" name="date_conf" id="tentative_confirmation_date"
                                            class="form-control" readonly style="background:#f8f9fa" />
                                    </div>
                                    <div class="col-12 mb-5">
                                        <label class="form-label">Note</label>
                                        <textarea class="form-control" name="emp_note" id="emp_note"
                                            placeholder="Describe the extra details..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: Personal -->
                            <div class="step-panel" id="step-2">
                                <p class="section-label"><i class="bi bi-person me-2"></i>Step 2 &mdash; Personal Information
                                </p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Father's Name</label>
                                        <input type="text" name="fathers_name" id="fathers_name" class="form-control"
                                            placeholder="Father's full name" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mother's Name</label>
                                        <input name="mothers_name" id="mothers_name" type="text" class="form-control"
                                            placeholder="Mother's full name" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Religion</label>
                                        <select name="religion" id="religion" class="form-select">
                                            <option value="">Select religion</option>
                                            <option>Islam</option>
                                            <option>Hinduism</option>
                                            <option>Christianity</option>
                                            <option>Buddhism</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" id="gender_step2" class="form-select">
                                            <option value="">Select gender</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Marital Status</label>
                                        <select name="merital_status" id="merital_status" class="form-select">
                                            <option value="">Select status</option>
                                            <option>Single</option>
                                            <option>Married</option>
                                            <option>Divorced</option>
                                            <option>Widowed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Blood Group</label>
                                        <select name="blood_group" id="blood_group" class="form-select">
                                            <option value="">Select blood group</option>
                                            <option>A+</option>
                                            <option>A-</option>
                                            <option>B+</option>
                                            <option>B-</option>
                                            <option>AB+</option>
                                            <option>AB-</option>
                                            <option>O+</option>
                                            <option>O-</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Employee Status</label>
                                        <select name="empl_status" id="empl_status" class="form-select">
                                            <option value="">Select status</option>
                                            <option>Active</option>
                                            <option>Inactive</option>
                                            <option>On Leave</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Upload Picture</label>
                                        <div>
                                            <img id="currentPicPreview" class="current-pic-preview" src=""
                                                alt="Current photo" />
                                        </div>
                                        <div class="upload-box" onclick="document.getElementById('picUpload').click()">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                            <div class="up-label">Click to upload a new employee photo (optional)</div>
                                            <div class="up-hint">JPG, PNG &mdash; max 5 MB. Leave empty to keep current photo.
                                            </div>
                                        </div>
                                        <input name="empl_picture" type="file" id="picUpload" accept="image/*"
                                            style="display:none" onchange="showFile(this,'picChosen')" />
                                        <div class="file-chosen" id="picChosen"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: Identification -->
                            <div class="step-panel" id="step-3">
                                <p class="section-label"><i class="bi bi-card-text me-2"></i>Step 3 &mdash; Identification
                                    Details</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">National ID</label>
                                        <input type="text" name="national_id" id="national_id" class="form-control"
                                            placeholder="NID number" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Birth Registration ID</label>
                                        <input type="text" name="birth_id" id="birth_id" class="form-control"
                                            placeholder="Birth reg. number" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Passport No.</label>
                                        <input type="text" name="passport_no" id="passport_no" class="form-control"
                                            placeholder="Passport number" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Driving License</label>
                                        <input type="text" name="driving_license" id="driving_license" class="form-control"
                                            placeholder="License number" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">TIN No.</label>
                                        <input type="text" name="tin_no" id="tin_no" class="form-control"
                                            placeholder="Tax ID number" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mobile No.</label>
                                        <input type="tel" name="mobile_no" id="mobile_no" class="form-control"
                                            placeholder="+880 1XXXXXXXXX" required />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email ID</label>
                                        <input type="email" name="email_id" id="email_id_step3" class="form-control"
                                            placeholder="email@example.com" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nationality</label>
                                        <input type="text" name="nationality" id="nationality" class="form-control"
                                            placeholder="e.g. Bangladeshi" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" onfocus="this.showPicker()" name="date_of_birth" id="date_of_birth"
                                            class="form-control" />
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 4: Address -->
                            <div class="step-panel" id="step-4">
                                <p class="section-label"><i class="bi bi-geo-alt me-2"></i>Step 4 &mdash; Address Details</p>
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-house-fill me-2" style="color:var(--brand);font-size:14px"></i>
                                        <span class="addr-title">Permanent Address</span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">House / Road</label>
                                            <input type="text" class="form-control" name="per_house" id="perm-house"
                                                placeholder="House no., Road, Village/Area" oninput="syncAddr()" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Division</label>
                                            <select name="per_division" class="form-select" id="perm-div" onchange="syncAddr()">
                                                <option value="">Select division</option>
                                                <option>Dhaka</option>
                                                <option>Chittagong</option>
                                                <option>Rajshahi</option>
                                                <option>Khulna</option>
                                                <option>Barisal</option>
                                                <option>Sylhet</option>
                                                <option>Rangpur</option>
                                                <option>Mymensingh</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">District</label>
                                            <input name="per_district" type="text" class="form-control" id="perm-dist"
                                                placeholder="District" oninput="syncAddr()" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Upazilla</label>
                                            <input name="per_upazilla" type="text" class="form-control" id="perm-upa"
                                                placeholder="Upazilla" oninput="syncAddr()" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Post-Office</label>
                                            <input name="per_post" type="text" class="form-control" id="perm-post"
                                                placeholder="Post Office Name With Post Code" oninput="syncAddr()" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Post Code</label>
                                            <input name="per_post_code" type="text" class="form-control" id="perm-post-code"
                                                placeholder="Post Office" oninput="syncAddr()" />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-pin-map-fill me-2" style="color:var(--brand);font-size:14px"></i>
                                            <span class="addr-title">Present Address</span>
                                        </div>
                                        <label class="same-check-label d-flex align-items-center gap-2">
                                            <input type="checkbox" id="sameAddr" onchange="toggleSame()" />
                                            Same as permanent address
                                        </label>
                                    </div>
                                    <div class="row g-3" id="presentAddrFields">
                                        <div class="col-12">
                                            <label class="form-label">House / Road</label>
                                            <input type="text" name="pre_house" class="form-control" id="pres-house"
                                                placeholder="House no., Road, Village/Area" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Division</label>
                                            <select name="pre_division" class="form-select" id="pres-div">
                                                <option value="">Select division</option>
                                                <option>Dhaka</option>
                                                <option>Chittagong</option>
                                                <option>Rajshahi</option>
                                                <option>Khulna</option>
                                                <option>Barisal</option>
                                                <option>Sylhet</option>
                                                <option>Rangpur</option>
                                                <option>Mymensingh</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">District</label>
                                            <input name="pre_district" type="text" class="form-control" id="pres-dist"
                                                placeholder="District" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Upazilla</label>
                                            <input name="pre_upazilla" type="text" class="form-control" id="pres-upa"
                                                placeholder="Upazilla" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Post-Office</label>
                                            <input name="pre_post" type="text" class="form-control" id="pres-post"
                                                placeholder="Post Office Name With Post Code" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Post Code</label>
                                            <input name="pre_post_code" type="text" class="form-control" id="pres-post-code"
                                                placeholder="Post Code" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 5: Education -->
                            <div class="step-panel" id="step-5">
                                <p class="section-label"><i class="bi bi-mortarboard me-2"></i>Step 5 &mdash; Educational
                                    Background</p>
                                <div class="table-responsive">
                                    <table class="edu-table">
                                        <thead>
                                            <tr>
                                                <th style="min-width:110px">Examination</th>
                                                <th style="min-width:150px">Institution</th>
                                                <th style="min-width:120px">Major Subject</th>
                                                <th style="min-width:140px">Board / University</th>
                                                <th style="min-width:110px">Academic Year</th>
                                                <th style="min-width:90px">Result</th>
                                                <th style="width:40px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="eduBody"></tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <button class="btn-add-row" onclick="addEduRow()"><i class="bi bi-plus-circle me-1"></i> Add
                                        Row</button>
                                </div>
                            </div>

                            <!-- STEP 6: Training Experience -->
                            <div class="step-panel" id="step-6">
                                <p class="section-label"><i class="bi bi-card-text me-2"></i>Step 6 &mdash; Training Experience
                                </p>
                                <div class="table-responsive">
                                    <table class="train-table">
                                        <thead>
                                            <tr>
                                                <th style="min-width:110px">Course Name</th>
                                                <th style="min-width:150px">Course Start Date</th>
                                                <th style="min-width:120px">Course End Date</th>
                                                <th style="min-width:140px">Course Duration</th>
                                                <th style="min-width:110px">Institution</th>
                                                <th style="min-width:90px">Institution Address</th>
                                                <th style="min-width:90px">Result</th>
                                                <th style="width:40px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trainBody"></tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <button class="btn-add-row" onclick="addTrainRow()"><i class="bi bi-plus-circle me-1"></i>
                                        Add Row</button>
                                </div>
                            </div>

                            <!-- STEP 7: Job Experience -->
                            <div class="step-panel" id="step-7">
                                <p class="section-label"><i class="bi bi-card-text me-2"></i>Step 7 &mdash; Job Experience</p>
                                <div class="table-responsive">
                                    <table class="train-table">
                                        <thead>
                                            <tr>
                                                <th style="min-width:110px">Organization Name</th>
                                                <th style="min-width:150px">Project Name</th>
                                                <th style="min-width:120px">Company Location</th>
                                                <th style="min-width:140px">From Date</th>
                                                <th style="min-width:110px">To Date</th>
                                                <th style="min-width:90px">Job Responsibility</th>
                                                <th style="width:40px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="jobBody"></tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <button class="btn-add-row" onclick="addJobRow()"><i class="bi bi-plus-circle me-1"></i> Add
                                        Row</button>
                                </div>
                            </div>

                            <!-- STEP 8: Guarantor -->
                            <div class="step-panel" id="step-8">
                                <p class="section-label"><i class="bi bi-person me-2"></i>Step 8 &mdash; Guarantor Information
                                </p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Guarantor Name</label>
                                        <input type="text" class="form-control" id="guerontor_name" name="guerontor_name"
                                            placeholder="Guarantor's full name" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">NID or Passport Number</label>
                                        <input type="text" class="form-control" id="nid_passporta" name="nid_passport"
                                            pattern="^(\d{10}|\d{17}|[A-Za-z]{2}\d{7})$" maxlength="17"
                                            placeholder="NID (10 or 17 digits) or Passport (e.g. AB1234567)"
                                            oninput="validateNidPassport(this)" />
                                        <div class="invalid-feedback" id="nid_passport_msg">
                                            Enter a valid NID (10 or 17 digits) or Passport number
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Birth Date</label>
                                        <input type="date" onfocus="this.showPicker()" class="form-control"
                                            id="guar_date_of_birth" name="date_of_birth" placeholder="Date of Birth" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" id="guar_gender" class="form-select">
                                            <option value="">Select gender</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone_no" name="phone_no"
                                            placeholder="Phone Number" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email Id</label>
                                        <input type="text" class="form-control" id="guar_email_id" name="email_id"
                                            placeholder="Email ID" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Occupation</label>
                                        <input type="text" class="form-control" id="occupation" name="occupation"
                                            placeholder="Occupation" />
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Upload Picture</label>
                                        <div>
                                            <img id="guarPicPreview" class="guar-pic-preview" src="" alt="Current photo" />
                                        </div>
                                        <div class="upload-box" onclick="document.getElementById('picUploadGuar').click()">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                            <div class="up-label">Click to upload a new guarantor photo (optional)</div>
                                            <div class="up-hint">JPG, PNG &mdash; max 5 MB. Leave empty to keep current photo.
                                            </div>
                                        </div>
                                        <input name="guar_picture" type="file" id="picUploadGuar" accept="image/*"
                                            style="display:none" onchange="showFile(this,'picChosenGuar')" />
                                        <div class="file-chosen" id="picChosenGuar"></div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-house-fill me-2" style="color:var(--brand);font-size:14px"></i>
                                            <span class="addr-title">Permanent Address</span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Parmanent Address</label>
                                                <input type="text" class="form-control" id="guar-perm-house"
                                                    name="permanent_address"
                                                    placeholder="House no., Road, Village/Area, Division, District, Upazila, Thana"
                                                    oninput="syncGuarAddr()" />
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-pin-map-fill me-2"
                                                    style="color:var(--brand);font-size:14px"></i>
                                                <span class="addr-title">Present Address</span>
                                            </div>
                                            <label class="same-check-label d-flex align-items-center gap-2">
                                                <input type="checkbox" id="sameGuarAddr" onchange="toggleSameGuar()" />
                                                Same as permanent address
                                            </label>
                                        </div>
                                        <div class="row g-3" id="presentGuarAddrFields">
                                            <div class="col-12">
                                                <label class="form-label">House / Road</label>
                                                <input name="present_address" type="text" class="form-control"
                                                    id="guar-pres-house"
                                                    placeholder="House no., Road, Village/Area, Division, District, Upazila, Thana" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 9: Nominee -->
                            <div class="step-panel" id="step-9">
                                <p class="section-label"><i class="bi bi-people me-2"></i>Step 9 &mdash; Nominee Information</p>
                                <div class="card border-0 mb-4" style="background:#f8f9fa;border-radius:10px">
                                    <div class="card-body py-3">
                                        <div class="row g-3 text-center">
                                            <div class="col-md-3 col-6">
                                                <div class="fw-600" style="font-size:12px;color:#6c757d;margin-bottom:4px">
                                                    Employee PF</div>
                                                <div class="progress" style="height:8px;border-radius:4px">
                                                    <div class="progress-bar" id="bar_emp_pf"
                                                        style="background:var(--brand);width:0%"></div>
                                                </div>
                                                <div style="font-size:12px;margin-top:4px"><span id="total_emp_pf">0</span>% /
                                                    100%</div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="fw-600" style="font-size:12px;color:#6c757d;margin-bottom:4px">
                                                    Gratuity</div>
                                                <div class="progress" style="height:8px;border-radius:4px">
                                                    <div class="progress-bar" id="bar_gratuity"
                                                        style="background:#0d6efd;width:0%"></div>
                                                </div>
                                                <div style="font-size:12px;margin-top:4px"><span id="total_gratuity">0</span>% /
                                                    100%</div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="fw-600" style="font-size:12px;color:#6c757d;margin-bottom:4px">Staff
                                                    Welfare Fund</div>
                                                <div class="progress" style="height:8px;border-radius:4px">
                                                    <div class="progress-bar" id="bar_staff_welfare_fund"
                                                        style="background:#fd7e14;width:0%"></div>
                                                </div>
                                                <div style="font-size:12px;margin-top:4px"><span
                                                        id="total_staff_welfare_fund">0</span>% / 100%</div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="fw-600" style="font-size:12px;color:#6c757d;margin-bottom:4px">Other
                                                    Benefit</div>
                                                <div class="progress" style="height:8px;border-radius:4px">
                                                    <div class="progress-bar" id="bar_other_benifit"
                                                        style="background:#6f42c1;width:0%"></div>
                                                </div>
                                                <div style="font-size:12px;margin-top:4px"><span
                                                        id="total_other_benifit">0</span>% / 100%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="nomineeContainer"></div>
                                <button class="btn-add-row mt-2" onclick="addNominee()"><i class="bi bi-plus-circle me-1"></i>
                                    Add Nominee</button>
                            </div>

                        </div><!-- /formContent -->

                        <div class="form-footer d-flex justify-content-between align-items-center">
                            <div>
                                <button class="btn btn-outline-secondary btn-sm" id="prevBtn" onclick="prevStep()"
                                    style="display:none">
                                    <i class="bi bi-arrow-left me-1"></i>Previous
                                </button>
                            </div>
                            <span class="progress-text" id="progressTxt">Step 1 of 9</span>
                            <div>
                                <button class="btn btn-brand btn-sm" id="nextBtn" onclick="nextStep()">
                                    Next <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                    </div><!-- /form-card -->
                </div><!-- /container -->
            </div>

            <script>
                const EMPLOYEE_ID = <?php echo json_encode($edit_employee_id); ?>;
                let currentStep = 1;
                const totalSteps = 9;
                let nomineeCount = 0;
                const PCT_FIELDS = ['emp_pf', 'gratuity', 'staff_welfare_fund', 'other_benifit'];

                async function loadEmployeeData() {
                    try {
                        const res = await fetch('empl_get_one.php?employee_id=' + encodeURIComponent(EMPLOYEE_ID));
                        const data = await res.json();

                        if (!data.success) {
                            alert('Error loading employee: ' + data.message);
                            return;
                        }

                        fillForm(data);

                        document.getElementById('formLoadingMsg').style.display = 'none';
                        document.getElementById('formContent').style.display = '';

                        const firstIncomplete = findFirstIncompleteStep();
                        goTo(firstIncomplete);

                    } catch (err) {
                        document.getElementById('formLoadingMsg').innerHTML =
                            '<p class="text-danger"><i class="bi bi-exclamation-circle me-2"></i>Network error loading employee data.</p>';
                        console.error(err);
                    }
                }

                function setVal(id, val) {
                    const el = document.getElementById(id);
                    if (el) el.value = val ?? '';
                }

                function fillForm(data) {
                    const gen = data.gen || {};
                    const identity = data.identity || {};
                    const address = data.address || {};
                    const guarantor = data.guarantor || {};

                    setVal('emp_joining_date', gen.emp_joining_date);
                    setVal('employee_name', gen.employee_name);
                    setVal('work_station', gen.work_station);
                    setVal('department', gen.department);
                    setVal('designation', gen.designation);
                    setVal('project_name', gen.project_name);
                    setVal('emploee_type', gen.emploee_type);
                    setVal('security_money', gen.security_money);
                    setVal('deposit_money', gen.deposit_money);
                    setVal('probation_period', gen.probation_period);
                    setVal('tentative_confirmation_date', gen.date_conf);
                    setVal('emp_note', gen.emp_note);

                    setVal('fathers_name', gen.fathers_name);
                    setVal('mothers_name', gen.mothers_name);
                    setVal('religion', gen.religion);
                    setVal('gender_step2', gen.gender);
                    setVal('merital_status', gen.merital_status);
                    setVal('blood_group', gen.blood_group);
                    setVal('empl_status', gen.empl_status);

                    if (gen.empl_picture) {
                        const img = document.getElementById('currentPicPreview');
                        img.src = 'employee_upload/employees/' + gen.empl_picture;
                        img.style.display = 'block';
                    }

                    setVal('national_id', identity.national_id);
                    setVal('birth_id', identity.birth_id);
                    setVal('passport_no', identity.passport_no);
                    setVal('driving_license', identity.driving_license);
                    setVal('tin_no', identity.tin_no);
                    setVal('mobile_no', identity.mobile_no);
                    setVal('email_id_step3', identity.email_id);
                    setVal('nationality', identity.nationality);
                    setVal('date_of_birth', identity.date_of_birth);

                    setVal('perm-house', address.per_house);
                    setVal('perm-div', address.per_division);
                    setVal('perm-dist', address.per_district);
                    setVal('perm-upa', address.per_upazilla);
                    setVal('perm-post', address.per_post);
                    setVal('perm-post-code', address.per_post_code);
                    setVal('pres-house', address.pre_house);
                    setVal('pres-div', address.pre_division);
                    setVal('pres-dist', address.pre_district);
                    setVal('pres-upa', address.pre_upazilla);
                    setVal('pres-post', address.pre_post);
                    setVal('pres-post-code', address.pre_post_code);

                    const eduBody = document.getElementById('eduBody');
                    eduBody.innerHTML = '';
                    if (data.education && data.education.length > 0) {
                        data.education.forEach(edu => addEduRow(edu));
                    } else {
                        addEduRow();
                    }

                    const trainBody = document.getElementById('trainBody');
                    trainBody.innerHTML = '';
                    if (data.training && data.training.length > 0) {
                        data.training.forEach(t => addTrainRow(t));
                    } else {
                        addTrainRow();
                    }

                    const jobBody = document.getElementById('jobBody');
                    jobBody.innerHTML = '';
                    if (data.experience && data.experience.length > 0) {
                        data.experience.forEach(e => addJobRow(e));
                    } else {
                        addJobRow();
                    }

                    setVal('guerontor_name', guarantor.guerontor_name);
                    setVal('nid_passporta', guarantor.nid_passport);
                    setVal('guar_date_of_birth', guarantor.date_of_birth);
                    setVal('guar_gender', guarantor.gender);
                    setVal('phone_no', guarantor.phone_no);
                    setVal('guar_email_id', guarantor.email_id);
                    setVal('guar-perm-house', guarantor.permanent_address);
                    setVal('guar-pres-house', guarantor.present_address);
                    setVal('occupation', guarantor.occupation);

                    if (guarantor.picture) {
                        const gimg = document.getElementById('guarPicPreview');
                        gimg.src = 'employee_upload/guarantors/' + guarantor.picture;
                        gimg.style.display = 'block';
                    }

                    const nomineeContainer = document.getElementById('nomineeContainer');
                    nomineeContainer.innerHTML = '';
                    nomineeCount = 0;
                    if (data.nominee && data.nominee.length > 0) {
                        data.nominee.forEach(n => addNominee(n));
                    }
                    updateAllPercentages();
                }

                function findFirstIncompleteStep() {
                    for (let s = 1; s <= totalSteps; s++) {
                        const panel = document.getElementById('step-' + s);
                        const requiredFields = panel.querySelectorAll('[required]');
                        for (const field of requiredFields) {
                            if (!field.value || !field.value.trim()) {
                                return s;
                            }
                        }
                    }
                    return 1;
                }

                function goTo(n) {
                    document.getElementById('step-' + currentStep).classList.remove('active');

                    const oldNav = document.querySelector('[data-step="' + currentStep + '"]');
                    oldNav.classList.remove('active');
                    if (n > currentStep) oldNav.classList.add('completed');

                    currentStep = n;

                    document.getElementById('step-' + currentStep).classList.add('active');
                    const newNav = document.querySelector('[data-step="' + currentStep + '"]');
                    newNav.classList.add('active');
                    newNav.classList.remove('completed');

                    for (let i = 1; i < currentStep; i++) {
                        const nb = document.querySelector('[data-step="' + i + '"]');
                        if (nb) { nb.classList.remove('active'); nb.classList.add('completed'); }
                    }

                    document.getElementById('progressTxt').textContent = 'Step ' + currentStep + ' of ' + totalSteps;
                    document.getElementById('prevBtn').style.display = currentStep > 1 ? '' : 'none';

                    const nextBtn = document.getElementById('nextBtn');
                    if (currentStep === totalSteps) {
                        nextBtn.style.display = 'none';
                    } else {
                        nextBtn.style.display = '';
                        nextBtn.innerHTML = 'Next <i class="bi bi-arrow-right ms-1"></i>';
                    }

                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                function nextStep() {
                    if (!validateStep(currentStep)) return;
                    if (currentStep < totalSteps) goTo(currentStep + 1);
                }

                function prevStep() {
                    if (currentStep > 1) goTo(currentStep - 1);
                }

                function handleCancel() {
                    if (confirm('Discard changes and go back?')) {
                        history.back();
                    }
                }

                async function handleSave() {
                    if (!validateNominees()) return;

                    const formData = new FormData();
                    formData.append('employee_id', EMPLOYEE_ID);

                    formData.append('emp_joining_date', document.getElementById('emp_joining_date').value);
                    formData.append('employee_name', document.getElementById('employee_name').value);
                    formData.append('work_station', document.getElementById('work_station').value);
                    formData.append('department', document.getElementById('department').value);
                    formData.append('designation', document.getElementById('designation').value);
                    formData.append('project_name', document.getElementById('project_name').value);
                    formData.append('emploee_type', document.getElementById('emploee_type').value);
                    formData.append('security_money', document.getElementById('security_money').value);
                    formData.append('deposit_money', document.getElementById('deposit_money').value);
                    formData.append('probation_period', document.getElementById('probation_period').value);
                    formData.append('date_conf', document.getElementById('tentative_confirmation_date').value);
                    formData.append('emp_note', document.getElementById('emp_note').value);

                    formData.append('fathers_name', document.getElementById('fathers_name').value);
                    formData.append('mothers_name', document.getElementById('mothers_name').value);
                    formData.append('religion', document.getElementById('religion').value);
                    formData.append('gender', document.getElementById('gender_step2').value);
                    formData.append('merital_status', document.getElementById('merital_status').value);
                    formData.append('blood_group', document.getElementById('blood_group').value);
                    formData.append('empl_status', document.getElementById('empl_status').value);

                    const pic = document.getElementById('picUpload').files[0];
                    if (pic) formData.append('empl_picture', pic);

                    formData.append('national_id', document.getElementById('national_id').value);
                    formData.append('birth_id', document.getElementById('birth_id').value);
                    formData.append('passport_no', document.getElementById('passport_no').value);
                    formData.append('driving_license', document.getElementById('driving_license').value);
                    formData.append('tin_no', document.getElementById('tin_no').value);
                    formData.append('mobile_no', document.getElementById('mobile_no').value);
                    formData.append('email_id', document.getElementById('email_id_step3').value);
                    formData.append('nationality', document.getElementById('nationality').value);
                    formData.append('date_of_birth', document.getElementById('date_of_birth').value);

                    formData.append('per_house', document.getElementById('perm-house').value);
                    formData.append('per_division', document.getElementById('perm-div').value);
                    formData.append('per_district', document.getElementById('perm-dist').value);
                    formData.append('per_upazilla', document.getElementById('perm-upa').value);
                    formData.append('per_post', document.getElementById('perm-post').value);
                    formData.append('per_post_code', document.getElementById('perm-post-code').value);
                    formData.append('pre_house', document.getElementById('pres-house').value);
                    formData.append('pre_division', document.getElementById('pres-div').value);
                    formData.append('pre_district', document.getElementById('pres-dist').value);
                    formData.append('pre_upazilla', document.getElementById('pres-upa').value);
                    formData.append('pre_post', document.getElementById('pres-post').value);
                    formData.append('pre_post_code', document.getElementById('pres-post-code').value);

                    document.querySelectorAll('#eduBody tr').forEach((row, i) => {
                        const cols = row.querySelectorAll('input, select');
                        formData.append(`education[${i}][examination]`, cols[0]?.value || '');
                        formData.append(`education[${i}][institution]`, cols[1]?.value || '');
                        formData.append(`education[${i}][major_subject]`, cols[2]?.value || '');
                        formData.append(`education[${i}][board_university]`, cols[3]?.value || '');
                        formData.append(`education[${i}][academic_year]`, cols[4]?.value || '');
                        formData.append(`education[${i}][result]`, cols[5]?.value || '');
                    });

                    document.querySelectorAll('#trainBody tr').forEach((row, i) => {
                        const cols = row.querySelectorAll('input');
                        formData.append(`training[${i}][course_name]`, cols[0]?.value || '');
                        formData.append(`training[${i}][course_stard_date]`, cols[1]?.value || '');
                        formData.append(`training[${i}][course_end_date]`, cols[2]?.value || '');
                        formData.append(`training[${i}][course_duration]`, cols[3]?.value || '');
                        formData.append(`training[${i}][institution_name]`, cols[4]?.value || '');
                        formData.append(`training[${i}][institution_address]`, cols[5]?.value || '');
                        formData.append(`training[${i}][result]`, cols[6]?.value || '');
                    });

                    document.querySelectorAll('#jobBody tr').forEach((row, i) => {
                        const cols = row.querySelectorAll('input');
                        formData.append(`experience[${i}][org_name]`, cols[0]?.value || '');
                        formData.append(`experience[${i}][project_name]`, cols[1]?.value || '');
                        formData.append(`experience[${i}][company_location]`, cols[2]?.value || '');
                        formData.append(`experience[${i}][from_date]`, cols[3]?.value || '');
                        formData.append(`experience[${i}][to_date]`, cols[4]?.value || '');
                        formData.append(`experience[${i}][job_respons]`, cols[5]?.value || '');
                    });

                    formData.append('guerontor_name', document.getElementById('guerontor_name').value);
                    formData.append('nid_passport', document.getElementById('nid_passporta').value);
                    formData.append('guar_date_of_birth', document.getElementById('guar_date_of_birth').value);
                    formData.append('guar_gender', document.getElementById('guar_gender').value);
                    formData.append('phone_no', document.getElementById('phone_no').value);
                    formData.append('guar_email_id', document.getElementById('guar_email_id').value);
                    formData.append('permanent_address', document.getElementById('guar-perm-house').value);
                    formData.append('present_address', document.getElementById('guar-pres-house').value);
                    formData.append('occupation', document.getElementById('occupation').value);

                    const picGuar = document.getElementById('picUploadGuar').files[0];
                    if (picGuar) formData.append('guar_picture', picGuar);

                    // Nominee cards - generic collection matching new form's approach,
                    // plus existing-picture passthrough when no new file chosen.
                    document.querySelectorAll('.nominee-card').forEach((card) => {
                        const idx = card.id.replace('nominee_card_', '');

                        card.querySelectorAll('input[name], select[name]').forEach(field => {
                            if (field.type !== 'file') {
                                formData.append(field.name, field.value);
                            }
                        });

                        const nomPicInput = document.getElementById('nom_pic_' + idx);
                        if (nomPicInput && nomPicInput.files[0]) {
                            formData.append('nominee_picture' + idx, nomPicInput.files[0]);
                        } else {
                            const existingPicHolder = document.getElementById('nom_existing_pic_' + idx);
                            if (existingPicHolder && existingPicHolder.value) {
                                formData.append('nominee_existing_picture_' + idx, existingPicHolder.value);
                            }
                        }
                    });

                    try {
                        const btn = document.querySelector('.btn-brand[onclick="handleSave()"]');
                        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...'; }

                        const response = await fetch('form_update.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert('Success: ' + result.message);
                            history.back();
                        } else {
                            alert('Error: ' + result.message);
                            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-floppy-fill me-1"></i>Save &amp; Close'; }
                        }
                    } catch (err) {
                        alert('Network error: ' + err.message);
                        const btn = document.querySelector('.btn-brand[onclick="handleSave()"]');
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-floppy-fill me-1"></i>Save &amp; Close'; }
                    }
                }

                function showFile(input, labelId) {
                    const label = document.getElementById(labelId);
                    if (input.files && input.files[0]) {
                        label.textContent = '✓ ' + input.files[0].name;
                        label.style.display = 'block';
                    }
                }

                function toggleSame() {
                    const checked = document.getElementById('sameAddr').checked;
                    const presFields = ['pres-house', 'pres-div', 'pres-dist', 'pres-upa', 'pres-post', 'pres-post-code'];
                    if (checked) {
                        document.getElementById('pres-house').value = document.getElementById('perm-house').value;
                        document.getElementById('pres-div').value = document.getElementById('perm-div').value;
                        document.getElementById('pres-dist').value = document.getElementById('perm-dist').value;
                        document.getElementById('pres-upa').value = document.getElementById('perm-upa').value;
                        document.getElementById('pres-post').value = document.getElementById('perm-post').value;
                        document.getElementById('pres-post-code').value = document.getElementById('perm-post-code').value;
                        presFields.forEach(id => {
                            const el = document.getElementById(id);
                            el.disabled = true; el.style.opacity = '0.6'; el.style.background = '#f8f9fa';
                        });
                    } else {
                        presFields.forEach(id => {
                            const el = document.getElementById(id);
                            el.disabled = false; el.style.opacity = '1'; el.style.background = '';
                        });
                    }
                }

                function syncAddr() {
                    if (document.getElementById('sameAddr').checked) toggleSame();
                }

                function toggleSameGuar() {
                    const checked = document.getElementById('sameGuarAddr').checked;
                    const presFields = ['guar-pres-house'];
                    if (checked) {
                        document.getElementById('guar-pres-house').value = document.getElementById('guar-perm-house').value;
                        presFields.forEach(id => {
                            const el = document.getElementById(id);
                            el.disabled = true; el.style.opacity = '0.6'; el.style.background = '#f8f9fa';
                        });
                    } else {
                        presFields.forEach(id => {
                            const el = document.getElementById(id);
                            el.disabled = false; el.style.opacity = '1'; el.style.background = '';
                        });
                    }
                }

                function syncGuarAddr() {
                    if (document.getElementById('sameGuarAddr').checked) toggleSameGuar();
                }

                function addEduRow(data) {
                    data = data || {};
                    const tbody = document.getElementById('eduBody');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
            <td>
              <select>
                <option value="">Select</option>
                <option>SSC</option><option>HSC</option><option>Diploma</option>
                <option>Bachelor's</option><option>Master's</option><option>PhD</option><option>Other</option>
              </select>
            </td>
            <td><input type="text" placeholder="Institution" /></td>
            <td><input type="text" placeholder="Subject" /></td>
            <td><input type="text" placeholder="Board / University" /></td>
            <td><input type="text" placeholder="e.g. 2018-2019" /></td>
            <td><input type="text" placeholder="GPA / Grade" /></td>
            <td>
              <button class="del-row-btn" onclick="deleteRow(this,'eduBody')" title="Remove row">
                <i class="bi bi-trash3"></i>
              </button>
            </td>
          `;
                    tbody.appendChild(tr);

                    const cells = tr.querySelectorAll('input, select');
                    if (data.edu_examination) cells[0].value = data.edu_examination;
                    if (data.edu_institution) cells[1].value = data.edu_institution;
                    if (data.edu_msubject) cells[2].value = data.edu_msubject;
                    if (data.board_university) cells[3].value = data.board_university;
                    if (data.academic_year) cells[4].value = data.academic_year;
                    if (data.result) cells[5].value = data.result;
                }

                function deleteRow(btn, bodyId) {
                    const rows = document.getElementById(bodyId).querySelectorAll('tr');
                    if (rows.length > 1) {
                        btn.closest('tr').remove();
                    } else {
                        btn.closest('tr').querySelectorAll('input, select').forEach(el => el.value = '');
                    }
                }

                function addTrainRow(data) {
                    data = data || {};
                    const tbody = document.getElementById('trainBody');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
            <td><input type="text" placeholder="Course Name" /></td>
            <td><input type="date" placeholder="Course Start Date" /></td>
            <td><input type="date" placeholder="Course End Date" /></td>
            <td><input type="text" placeholder="Course Duration" /></td>
            <td><input type="text" placeholder="Institution Name" /></td>
            <td><input type="text" placeholder="Institution Address" /></td>
            <td><input type="text" placeholder="Result" /></td>
            <td>
              <button class="del-row-btn" onclick="deleteRow(this,'trainBody')" title="Remove row">
                <i class="bi bi-trash3"></i>
              </button>
            </td>
          `;
                    tbody.appendChild(tr);

                    const cells = tr.querySelectorAll('input');
                    if (data.course_name) cells[0].value = data.course_name;
                    if (data.course_stard_date) cells[1].value = data.course_stard_date;
                    if (data.course_end_date) cells[2].value = data.course_end_date;
                    if (data.course_duration) cells[3].value = data.course_duration;
                    if (data.institution_name) cells[4].value = data.institution_name;
                    if (data.institution_address) cells[5].value = data.institution_address;
                    if (data.result) cells[6].value = data.result;
                }

                function addJobRow(data) {
                    data = data || {};
                    const tbody = document.getElementById('jobBody');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
            <td><input type="text" placeholder="Organization Name" /></td>
            <td><input type="text" placeholder="Project Name" /></td>
            <td><input type="text" placeholder="Company Location" /></td>
            <td><input type="date" placeholder="From Date" /></td>
            <td><input type="date" placeholder="To Date" /></td>
            <td><input type="text" placeholder="Job Responsibility" /></td>
            <td>
              <button class="del-row-btn" onclick="deleteRow(this,'jobBody')" title="Remove row">
                <i class="bi bi-trash3"></i>
              </button>
            </td>
          `;
                    tbody.appendChild(tr);

                    const cells = tr.querySelectorAll('input');
                    if (data.org_name) cells[0].value = data.org_name;
                    if (data.project_name) cells[1].value = data.project_name;
                    if (data.company_location) cells[2].value = data.company_location;
                    if (data.from_date) cells[3].value = data.from_date;
                    if (data.to_date) cells[4].value = data.to_date;
                    if (data.job_respons) cells[5].value = data.job_respons;
                }

                function validateStep(step) {
                    const panel = document.getElementById('step-' + step);
                    const requiredFields = panel.querySelectorAll('[required]');

                    let isValid = true;
                    let firstInvalid = null;

                    requiredFields.forEach(field => {
                        field.classList.remove('is-invalid');
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            isValid = false;
                            if (!firstInvalid) firstInvalid = field;
                        }
                    });

                    if (!isValid) {
                        firstInvalid.focus();
                        showValidationAlert(panel);
                    }

                    return isValid;
                }

                function showValidationAlert(panel) {
                    const old = panel.querySelector('.validation-alert');
                    if (old) old.remove();

                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger validation-alert mt-3';
                    alertDiv.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Please fill in all required fields before proceeding.';
                    panel.prepend(alertDiv);

                    setTimeout(() => alertDiv.remove(), 3000);
                }

                function validateNidPassport(input) {
                    const value = input.value.trim();
                    const nid10 = /^\d{10}$/;
                    const nid17 = /^\d{17}$/;
                    const passport = /^[A-Za-z]{2}\d{7}$/;
                    const isValid = nid10.test(value) || nid17.test(value) || passport.test(value);

                    if (!isValid && value.length > 0) {
                        input.classList.add('is-invalid');
                        input.classList.remove('is-valid');
                        const msg = document.getElementById('nid_passport_msg');
                        if (/^\d+$/.test(value)) {
                            msg.textContent = value.length < 10
                                ? `NID must be 10 or 17 digits. You entered ${value.length} digit(s).`
                                : value.length > 10 && value.length < 17
                                    ? `NID must be exactly 10 or 17 digits. You entered ${value.length}.`
                                    : value.length > 17
                                        ? 'NID cannot be more than 17 digits.'
                                        : 'Invalid NID number.';
                        } else {
                            msg.textContent = 'Passport must be 2 letters followed by 7 digits (e.g. AB1234567).';
                        }
                    } else if (isValid) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.remove('is-valid');
                    }
                }

                function addNominee(data) {
                    data = data || {};
                    nomineeCount++;
                    const idx = nomineeCount;
                    const container = document.getElementById('nomineeContainer');

                    const card = document.createElement('div');
                    card.className = 'nominee-card';
                    card.id = 'nominee_card_' + idx;
                    card.innerHTML = `
    <div class="nominee-header">
      <span class="nominee-title"><i class="bi bi-person-fill me-2" style="color:var(--brand)"></i>Nominee ${idx}</span>
      <button class="nominee-remove-btn" onclick="removeNominee(${idx})">
        <i class="bi bi-trash3 me-1"></i>Remove
      </button>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Nominee Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="nominee[${idx}][nominee_name]" placeholder="Full name" required />
      </div>
      <div class="col-md-4">
        <label class="form-label">Date of Birth</label>
        <input type="date" class="form-control" name="nominee[${idx}][birth_date]" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Contact No.</label>
        <input type="tel" class="form-control" name="nominee[${idx}][contact_no]" placeholder="+880 1XXXXXXXXX" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Relation</label>
        <select class="form-select" name="nominee[${idx}][relation]">
          <option value="">Select relation</option>
          <option>Father</option><option>Mother</option><option>Spouse</option>
          <option>Son</option><option>Daughter</option><option>Brother</option>
          <option>Sister</option><option>Other</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">NID / Birth Reg. No.</label>
        <input type="text" class="form-control" name="nominee[${idx}][nid_birth_reg]" placeholder="NID or Birth Registration" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Picture</label>
        <div>
          <img id="nom_pic_preview_${idx}" class="nominee-pic-preview" style="display:none" src="" alt="Current photo" />
        </div>
        <div class="nominee-upload-box" onclick="document.getElementById('nom_pic_${idx}').click()">
          <i class="bi bi-cloud-arrow-up"></i>
          Click to upload a new photo (optional)
        </div>
        <input type="file" id="nom_pic_${idx}" accept="image/*" style="display:none" onchange="showNomineePic(this, ${idx})" />
        <input type="hidden" id="nom_existing_pic_${idx}" value="" />
        <div id="nom_pic_name_${idx}" style="font-size:11px;color:var(--brand);margin-top:4px;display:none"></div>
      </div>

      <div class="col-12">
        <div class="section-label mb-3 mt-1">Benefit Allocation (%)</div>
        <div class="row g-3">
          <div class="col-md-3 col-6">
            <label class="form-label">Employee PF %</label>
            <div class="pct-input-wrap">
              <input type="number" class="form-control pct-field" min="0" max="100" step="0.01"
                name="nominee[${idx}][emp_pf]" data-field="emp_pf" data-idx="${idx}" placeholder="0" oninput="updatePercentage('emp_pf')" />
              <span class="pct-sign">%</span>
            </div>
            <div class="pct-msg text-danger mt-1" id="msg_emp_pf_${idx}" style="font-size:11px;display:none"></div>
          </div>
          <div class="col-md-3 col-6">
            <label class="form-label">Gratuity %</label>
            <div class="pct-input-wrap">
              <input type="number" class="form-control pct-field" min="0" max="100" step="0.01"
                name="nominee[${idx}][gratuity]" data-field="gratuity" data-idx="${idx}" placeholder="0" oninput="updatePercentage('gratuity')" />
              <span class="pct-sign">%</span>
            </div>
            <div class="pct-msg text-danger mt-1" id="msg_gratuity_${idx}" style="font-size:11px;display:none"></div>
          </div>
          <div class="col-md-3 col-6">
            <label class="form-label">Staff Welfare Fund %</label>
            <div class="pct-input-wrap">
              <input type="number" class="form-control pct-field" min="0" max="100" step="0.01"
                name="nominee[${idx}][staff_welfare_fund]" data-field="staff_welfare_fund" data-idx="${idx}" placeholder="0" oninput="updatePercentage('staff_welfare_fund')" />
              <span class="pct-sign">%</span>
            </div>
            <div class="pct-msg text-danger mt-1" id="msg_staff_welfare_fund_${idx}" style="font-size:11px;display:none"></div>
          </div>
          <div class="col-md-3 col-6">
            <label class="form-label">Other Benefit %</label>
            <div class="pct-input-wrap">
              <input type="number" class="form-control pct-field" min="0" max="100" step="0.01"
                name="nominee[${idx}][other_benifit]" data-field="other_benifit" data-idx="${idx}" placeholder="0" oninput="updatePercentage('other_benifit')" />
              <span class="pct-sign">%</span>
            </div>
            <div class="pct-msg text-danger mt-1" id="msg_other_benifit_${idx}" style="font-size:11px;display:none"></div>
          </div>
        </div>
      </div>
    </div>
  `;

                    container.appendChild(card);

                    if (data.nominee_name) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][nominee_name]"]`).value = data.nominee_name;
                    if (data.birth_date) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][birth_date]"]`).value = data.birth_date;
                    if (data.contact_no) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][contact_no]"]`).value = data.contact_no;
                    if (data.relation) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][relation]"]`).value = data.relation;
                    if (data.nid_birth_reg) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][nid_birth_reg]"]`).value = data.nid_birth_reg;
                    if (data.emp_pf) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][emp_pf]"]`).value = data.emp_pf;
                    if (data.gratuity) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][gratuity]"]`).value = data.gratuity;
                    if (data.staff_welfare_fund) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][staff_welfare_fund]"]`).value = data.staff_welfare_fund;
                    if (data.other_benifit) document.getElementById('nominee_card_' + idx).querySelector(`[name="nominee[${idx}][other_benifit]"]`).value = data.other_benifit;

                    if (data.picture) {
                        document.getElementById('nom_existing_pic_' + idx).value = data.picture;
                        const prev = document.getElementById('nom_pic_preview_' + idx);
                        prev.src = 'employee_upload/nominees/' + data.picture;
                        prev.style.display = 'block';
                    }

                    updateAllPercentages();
                }

                function removeNominee(idx) {
                    const card = document.getElementById('nominee_card_' + idx);
                    if (card) card.remove();
                    updateAllPercentages();
                }

                function showNomineePic(input, idx) {
                    const label = document.getElementById('nom_pic_name_' + idx);
                    if (input.files && input.files[0]) {
                        label.textContent = '✓ ' + input.files[0].name;
                        label.style.display = 'block';
                    }
                }

                function updatePercentage(fieldName) {
                    const inputs = document.querySelectorAll(`[data-field="${fieldName}"]`);
                    let total = 0;
                    inputs.forEach(input => { total += parseFloat(input.value) || 0; });
                    total = Math.round(total * 100) / 100;

                    const bar = document.getElementById('bar_' + fieldName);
                    const totalSpan = document.getElementById('total_' + fieldName);

                    if (bar) {
                        bar.style.width = Math.min(total, 100) + '%';
                        bar.style.background = total > 100 ? '#dc3545' : total === 100 ? 'var(--brand)' : '#fd7e14';
                    }
                    if (totalSpan) totalSpan.textContent = total;

                    inputs.forEach(input => {
                        const idx = input.getAttribute('data-idx');
                        const msg = document.getElementById('msg_' + fieldName + '_' + idx);
                        const val = parseFloat(input.value) || 0;

                        input.classList.remove('pct-over', 'pct-ok');

                        if (total > 100) {
                            input.classList.add('pct-over');
                            if (msg) {
                                msg.textContent = `Total exceeds 100%. Currently at ${total}%. Please reduce by ${(total - 100).toFixed(2)}%.`;
                                msg.style.display = 'block';
                            }
                        } else if (total === 100) {
                            input.classList.add('pct-ok');
                            if (msg) msg.style.display = 'none';
                        } else {
                            if (msg) {
                                const remaining = (100 - total).toFixed(2);
                                msg.textContent = remaining > 0 && val > 0 ? `${remaining}% remaining to allocate.` : '';
                                msg.style.display = remaining > 0 && val > 0 ? 'block' : 'none';
                                msg.style.color = '#fd7e14';
                            }
                        }
                    });
                }

                function updateAllPercentages() {
                    PCT_FIELDS.forEach(f => updatePercentage(f));
                }

                function validateNominees() {
                    let valid = true;
                    let msg = '';

                    PCT_FIELDS.forEach(field => {
                        const inputs = document.querySelectorAll(`[data-field="${field}"]`);
                        let total = 0;
                        inputs.forEach(i => total += parseFloat(i.value) || 0);
                        total = Math.round(total * 100) / 100;

                        const label = {
                            emp_pf: 'Employee PF',
                            gratuity: 'Gratuity',
                            staff_welfare_fund: 'Staff Welfare Fund',
                            other_benifit: 'Other Benefit'
                        }[field];

                        if (inputs.length > 0 && total !== 100 && total !== 0) {
                            msg += `- ${label}: ${total}% allocated (must be exactly 100% or 0%)\n`;
                            valid = false;
                        }
                        if (total > 100) {
                            msg += `- ${label}: exceeds 100%\n`;
                            valid = false;
                        }
                    });

                    if (!valid) {
                        alert('Percentage allocation error:\n\n' + msg);
                    }
                    return valid;
                }

                function calcConfirmDate() {
                    const joiningDate = document.getElementById('emp_joining_date').value;
                    const probationMonth = document.getElementById('probation_period').value;
                    const confirmField = document.getElementById('tentative_confirmation_date');

                    if (!joiningDate || !probationMonth) {
                        return;
                    }

                    const date = new Date(joiningDate);
                    date.setMonth(date.getMonth() + parseInt(probationMonth));

                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');

                    confirmField.value = `${year}-${month}-${day}`;
                }

                loadEmployeeData();
            </script>
        </body>

        </html>

        <?php include '../../include/footer.php'; ?>

        <?php
    } // end permission check
} // end session check
?>