<?php
session_start();
include('../../include/db_connect.php');
error_reporting(0);

if (strlen($_SESSION['login']) == 0) {
    header('location:../../index.php');
    exit;
} else {
    $PageID = 'P032';
    $ModulePage = 'P013';
    $ModuleID = 001;
    include('../../module_admin/user_query.php'); // adjust this path if your file lives elsewhere

    if (!in_array($ModulePage, $RolModule)) {
        header('location:../../include/user_permission_require.php');
        exit;
    } elseif (!in_array($PageID, $RoleUpdateViw)) {
        header('location:../../include/user_permission_require.php');
        exit;
    } else {
        include '../../include/sidebar.php';
        include '../../include/header.php';

        // ── Auto branch code (used for Add only; bypassed on Edit) ─────────
        $query_branch = mysqli_query($conn_ad, "SELECT MAX(branch_code) AS branchcode FROM office_branch WHERE 1");
        $BranchCodeMax = 1001;
        if ($result_branch = mysqli_fetch_assoc($query_branch)) {
            if (!empty($result_branch['branchcode'])) {
                $BranchCodeMax = $result_branch['branchcode'] + 1;
            }
        }

        // ── Edit mode detection ─────────────────────────────────────────────
        $is_edit = false;
        $edit_data = [];
        if (isset($_GET['edit_id'])) {
            $is_edit = true;
            $edit_id = mysqli_real_escape_string($conn_ad, $_GET['edit_id']);
            $query_edit = mysqli_query($conn_ad, "SELECT * FROM office_branch WHERE branch_code = '$edit_id'");
            $edit_data = mysqli_fetch_assoc($query_edit);

            if (!$edit_data) {
                header('location:office_branch.php');
                exit;
            }
        }

        $msgsc = '';

        // ── Insert / Update handler ──────────────────────────────────────────
        if (isset($_POST['submit'])) {
            $branch_name = mysqli_real_escape_string($conn_ad, $_POST['branch_name']);
            $area_code = mysqli_real_escape_string($conn_ad, $_POST['area_code']);
            $region_code = mysqli_real_escape_string($conn_ad, $_POST['region_code']);
            $zone_code = mysqli_real_escape_string($conn_ad, $_POST['zone_code']);
            $branch_op_date = mysqli_real_escape_string($conn_ad, $_POST['branch_op_date']);
            $mobile_no = mysqli_real_escape_string($conn_ad, $_POST['mobile_no']);
            $email_id = mysqli_real_escape_string($conn_ad, $_POST['email_id']);
            $address = mysqli_real_escape_string($conn_ad, $_POST['address']);
            $division_code = mysqli_real_escape_string($conn_ad, $_POST['division_code']);
            $district_code = mysqli_real_escape_string($conn_ad, $_POST['district_code']);
            $upazilla_code = mysqli_real_escape_string($conn_ad, $_POST['upazilla_code']);
            $branch_codeold = mysqli_real_escape_string($conn_ad, $_POST['branch_codeold']);
            $zonal_it = mysqli_real_escape_string($conn_ad, $_POST['zonal_it']);
            $zonal_ac = mysqli_real_escape_string($conn_ad, $_POST['zonal_acc']);
            $ho_it = mysqli_real_escape_string($conn_ad, $_POST['ho_ict']);
            $loc_longitude = mysqli_real_escape_string($conn_ad, $_POST['loc_longitude'] ?? '');
            $loc_latitude = mysqli_real_escape_string($conn_ad, $_POST['loc_latitude'] ?? '');

            if (isset($_POST['action_mode']) && $_POST['action_mode'] == 'update') {
                $branch_code = mysqli_real_escape_string($conn_ad, $_POST['branch_code']);

                $sql_update = "UPDATE office_branch SET 
                    branch_name = '$branch_name', area_code = '$area_code', region_code = '$region_code', 
                    zone_code = '$zone_code', branch_op_date = '$branch_op_date', mobile_no = '$mobile_no', 
                    email_id = '$email_id', address = '$address', division_code = '$division_code', 
                    district_code = '$district_code', upazilla_code = '$upazilla_code', branch_codeold = '$branch_codeold', 
                    zonal_it = '$zonal_it', zonal_ac = '$zonal_ac', ho_it = '$ho_it', 
                    loc_longitute = '$loc_longitude', loc_latitute = '$loc_latitude' 
                    WHERE branch_code = '$branch_code'";

                $query_action = mysqli_query($conn_ad, $sql_update);

                if ($query_action) {
                    // re-fetch so the form reflects exactly what's saved
                    $query_edit = mysqli_query($conn_ad, "SELECT * FROM office_branch WHERE branch_code = '$branch_code'");
                    $edit_data = mysqli_fetch_assoc($query_edit);
                    $msgsc = 'success_update';
                } else {
                    $msgsc = 'error';
                }
            } else {
                $branch_status = '1';

                $sql_insert = "INSERT INTO office_branch 
                    (branch_code, branch_name, branch_status, area_code, region_code, zone_code, 
                     branch_op_date, mobile_no, email_id, address, division_code, district_code, 
                     upazilla_code, branch_codeold, zonal_it, zonal_ac, ho_it, loc_longitute, loc_latitute) 
                    VALUES 
                    ('$BranchCodeMax', '$branch_name', '$branch_status', '$area_code', '$region_code', 
                     '$zone_code', '$branch_op_date', '$mobile_no', '$email_id', '$address', 
                     '$division_code', '$district_code', '$upazilla_code', '$branch_codeold',
                     '$zonal_it', '$zonal_ac', '$ho_it', '$loc_longitude', '$loc_latitude')";

                $query_action = mysqli_query($conn_ad, $sql_insert);

                if ($query_action) {
                    $msgsc = 'success_add';
                    $BranchCodeMax++;
                } else {
                    $msgsc = 'error';
                }
            }
        }
        ?>
        <link rel="stylesheet" href="../css/single_form.css">

        <div id="mainContent" class="main-content">
            <div class="container-fluid" style="max-width:1200px">

                <!-- Alert -->
                <?php if ($msgsc === 'success_add'): ?>
                    <div class="form-alert alert-success-custom" id="formAlert">Branch added successfully.</div>
                <?php elseif ($msgsc === 'success_update'): ?>
                    <div class="form-alert alert-success-custom" id="formAlert">Branch updated successfully.</div>
                <?php elseif ($msgsc === 'error'): ?>
                    <div class="form-alert alert-error-custom" id="formAlert">Error! Please check the information and try again.
                    </div>
                <?php endif; ?>

                <div class="branch-form-wrap">

                    <!-- Section Header -->
                    <div class="form-section-head">
                        <h5>
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#09976F" stroke-width="2.2"
                                aria-hidden="true">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                            <?= $is_edit ? 'Edit Branch Office (Code: ' . htmlspecialchars($edit_data['branch_code']) . ')' : 'Add New Branch Office' ?>
                        </h5>
                        <a href="office_branch.php" class="btn-back">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                aria-hidden="true">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                            Branch List
                        </a>
                    </div>

                    <!-- Form -->
                    <form method="post" action="">
                        <input type="hidden" name="action_mode" value="<?= $is_edit ? 'update' : 'insert' ?>">


                        <div class="form-grid">

                            <!-- Row 1: Code, Code Old, Branch Name, Zone -->
                            <div class="field-group">
                                <label>Branch Code <span class="req">*</span></label>
                                <input type="text" name="branch_code"
                                    value="<?php echo $is_edit ? $edit_data['branch_code'] : $BranchCodeMax; ?>"
                                    class="form-control form-input" readonly>
                            </div>

                            <div class="field-group">
                                <label>Branch Code (Old) <span class="req">*</span></label>
                                <input type="text" name="branch_codeold"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['branch_codeold']) : '' ?>"
                                    placeholder="Old branch code" required>
                            </div>

                            <div class="field-group">
                                <label>Branch Name <span class="req">*</span></label>
                                <input type="text" name="branch_name"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['branch_name']) : '' ?>"
                                    placeholder="Enter branch name" required>
                            </div>

                            <div class="field-group">
                                <label>Zone Name <span class="req">*</span></label>
                                <select id="zone" name="zone_code" required>
                                    <option value="">-- Select Zone --</option>
                                    <?php
                                    $q = mysqli_query($conn_ad, "SELECT * FROM office_zone");
                                    while ($row = mysqli_fetch_assoc($q)) {
                                        $sel = ($is_edit && $edit_data['zone_code'] == $row['zone_code']) ? 'selected' : '';
                                        echo '<option value="' . $row['zone_code'] . '" ' . $sel . '>' . $row['zone_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Row 2: Region, Area, Mobile, Email -->
                            <div class="field-group">
                                <label>Region Name <span class="req">*</span></label>
                                <select id="region" name="region_code" required>
                                    <option value="">-- Select Region --</option>
                                    <?php
                                    if ($is_edit) {
                                        $q_reg = mysqli_query($conn_ad, "SELECT * FROM office_region WHERE zone_code = '{$edit_data['zone_code']}'");
                                        while ($r_reg = mysqli_fetch_assoc($q_reg)) {
                                            $sel = ($edit_data['region_code'] == $r_reg['region_code']) ? 'selected' : '';
                                            echo '<option value="' . $r_reg['region_code'] . '" ' . $sel . '>' . $r_reg['region_name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>Area Name <span class="req">*</span></label>
                                <select id="area" name="area_code" required>
                                    <option value="">-- Select Area --</option>
                                    <?php
                                    if ($is_edit) {
                                        $q_area = mysqli_query($conn_ad, "SELECT * FROM office_area WHERE region_code = '{$edit_data['region_code']}'");
                                        while ($r_area = mysqli_fetch_assoc($q_area)) {
                                            $sel = ($edit_data['area_code'] == $r_area['area_code']) ? 'selected' : '';
                                            echo '<option value="' . $r_area['area_code'] . '" ' . $sel . '>' . $r_area['area_name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>Branch Mobile No <span class="req">*</span></label>
                                <input type="text" name="mobile_no"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['mobile_no']) : '' ?>"
                                    placeholder="01XXXXXXXXX" required>
                            </div>

                            <div class="field-group">
                                <label>Branch E-Mail <span class="req">*</span></label>
                                <input type="email" name="email_id"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['email_id']) : '' ?>"
                                    placeholder="branch@example.com" required>
                            </div>

                            <!-- Row 3: Address (span 2), Division, District -->
                            <div class="field-group span-2">
                                <label>Address <span class="req">*</span></label>
                                <input type="text" name="address"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['address']) : '' ?>"
                                    placeholder="Road # 001, House # 120, 1st floor" required>
                            </div>

                            <div class="field-group">
                                <label>Division Name <span class="req">*</span></label>
                                <select id="division" name="division_code" required>
                                    <option value="">-- Select Division --</option>
                                    <?php
                                    $qd = mysqli_query($conn_ad, "SELECT * FROM area_division");
                                    while ($rd = mysqli_fetch_assoc($qd)) {
                                        $sel = ($is_edit && $edit_data['division_code'] == $rd['division_code']) ? 'selected' : '';
                                        echo '<option value="' . $rd['division_code'] . '" ' . $sel . '>' . $rd['division_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>District Name <span class="req">*</span></label>
                                <select id="district" name="district_code" required>
                                    <option value="">-- Select District --</option>
                                    <?php
                                    // NOTE: area_district's actual column is the misspelled "disctrict_name" — keep as-is
                                    if ($is_edit) {
                                        $q_dist = mysqli_query($conn_ad, "SELECT * FROM area_district WHERE division_code = '{$edit_data['division_code']}'");
                                        while ($r_dist = mysqli_fetch_assoc($q_dist)) {
                                            $sel = ($edit_data['district_code'] == $r_dist['district_code']) ? 'selected' : '';
                                            echo '<option value="' . $r_dist['district_code'] . '" ' . $sel . '>' . $r_dist['disctrict_name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Row 4: Upazilla, Opening Date, Zonal IT, Zonal AC -->
                            <div class="field-group">
                                <label>Upazilla Name <span class="req">*</span></label>
                                <select id="upazilla" name="upazilla_code" required>
                                    <option value="">-- Select Upazilla --</option>
                                    <?php
                                    if ($is_edit) {
                                        $q_up = mysqli_query($conn_ad, "SELECT * FROM area_upazilla WHERE district_code = '{$edit_data['district_code']}'");
                                        while ($r_up = mysqli_fetch_assoc($q_up)) {
                                            $sel = ($edit_data['upazilla_code'] == $r_up['upazilla_code']) ? 'selected' : '';
                                            echo '<option value="' . $r_up['upazilla_code'] . '" ' . $sel . '>' . $r_up['upazilla_name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>Branch Opening Date <span class="req">*</span></label>
                                <input type="date" name="branch_op_date" id="myDatepicker2_1"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['branch_op_date']) : '' ?>" required>
                            </div>

                            <!-- Zonal IT — employee dropdown from conn_hrm -->
                            <div class="field-group">
                                <label>Zonal MIS &amp; ICT Name <span class="req">*</span></label>
                                <select name="zonal_it" required>
                                    <option value="">-- Select --</option>
                                    <?php
                                    $qZIT = mysqli_query($conn_hrm, "SELECT employee_id, employee_name FROM empl_gen WHERE department='S04' AND work_station != '000' ORDER BY employee_name");
                                    while ($rZIT = mysqli_fetch_assoc($qZIT)) {
                                        $sel = ($is_edit && $edit_data['zonal_it'] == $rZIT['employee_id']) ? 'selected' : '';
                                        echo '<option value="' . $rZIT['employee_id'] . '" ' . $sel . '>' . $rZIT['employee_id'] . ' - ' . $rZIT['employee_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Zonal AC — POST name must stay "zonal_acc" to match the handler -->
                            <div class="field-group">
                                <label>Zonal Accounts Officer <span class="req">*</span></label>
                                <select name="zonal_acc" required>
                                    <option value="">-- Select --</option>
                                    <?php
                                    $qZAC = mysqli_query($conn_hrm, "SELECT employee_id, employee_name FROM empl_gen WHERE department='S03' AND work_station != '000' ORDER BY employee_name");
                                    while ($rZAC = mysqli_fetch_assoc($qZAC)) {
                                        $sel = ($is_edit && $edit_data['zonal_ac'] == $rZAC['employee_id']) ? 'selected' : '';
                                        echo '<option value="' . $rZAC['employee_id'] . '" ' . $sel . '>' . $rZAC['employee_id'] . ' - ' . $rZAC['employee_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- HO IT — POST name must stay "ho_ict" to match the handler -->
                            <div class="field-group">
                                <label>HO MIS &amp; ICT Supervisor <span class="req">*</span></label>
                                <select name="ho_ict" required>
                                    <option value="">-- Select --</option>
                                    <?php
                                    $qHO = mysqli_query($conn_hrm, "SELECT employee_id, employee_name FROM empl_gen WHERE department='S04' AND work_station = '000' ORDER BY employee_name");
                                    while ($rHO = mysqli_fetch_assoc($qHO)) {
                                        $sel = ($is_edit && $edit_data['ho_it'] == $rHO['employee_id']) ? 'selected' : '';
                                        echo '<option value="' . $rHO['employee_id'] . '" ' . $sel . '>' . $rHO['employee_id'] . ' - ' . $rHO['employee_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Longitude — POST name "loc_longitude" (edit handler expects this) -->
                            <div class="field-group">
                                <label>Location (Longitude) <span class="req">*</span></label>
                                <input type="text" id="longitude" name="loc_longitude"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['loc_longitute']) : '' ?>"
                                    placeholder="Auto-detected via GPS" readonly required>
                            </div>

                            <!-- Latitude — POST name "loc_latitude" -->
                            <div class="field-group">
                                <label>Location (Latitude) <span class="req">*</span></label>
                                <input type="text" id="latitude" name="loc_latitude"
                                    value="<?= $is_edit ? htmlspecialchars($edit_data['loc_latitute']) : '' ?>"
                                    placeholder="Auto-detected via GPS" readonly required>
                            </div>

                            <!-- empty 4th cell to keep grid even -->
                            <div></div>

                            <!-- Divider -->
                            <hr class="form-divider">

                            <!-- Buttons -->
                            <div class="form-actions">
                                <button type="submit" name="submit" class="btn-submit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" aria-hidden="true">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <?= $is_edit ? 'Update' : 'Submit' ?>
                                </button>
                                <button type="reset" class="btn-reset-form">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" aria-hidden="true">
                                        <polyline points="1 4 1 10 7 10" />
                                        <path d="M3.51 15a9 9 0 1 0 .49-3.7" />
                                    </svg>
                                    Reset
                                </button>
                                <a href="office_branch.php" class="btn-cancel">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" aria-hidden="true">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                    Cancel
                                </a>
                            </div>

                        </div><!-- /form-grid -->
                    </form>

                </div><!-- /branch-form-wrap -->
            </div><!-- /container-fluid -->
        </div><!-- /main-content -->

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            // Prevent resubmission on refresh
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            /* ── GPS auto-fill (Add mode only — edit keeps the saved location) ── */
            <?php //if (!$is_edit): ?>

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        document.getElementById("latitude").value = pos.coords.latitude.toFixed(6);
                        document.getElementById("longitude").value = pos.coords.longitude.toFixed(6);
                    },
                    function (err) { console.warn("GPS error:", err.message); },
                    { enableHighAccuracy: true, timeout: 7000, maximumAge: 0 }
                );
            }

            <?php //endif; ?>

            // Auto-hide alert after 3s
            setTimeout(function () {
                const el = document.getElementById("formAlert");
                if (el) el.style.display = "none";
            }, 3000);

            $(document).ready(function () {

                // Zone → Region
                $('#zone').change(function () {
                    $.ajax({
                        url: "get_office.php", type: "POST",
                        data: { zone_code: $(this).val() },
                        success: function (data) {
                            $('#region').html(data);
                            $('#area').html('<option value="">-- Select Area --</option>');
                        }
                    });
                });

                // Region → Area
                $('#region').change(function () {
                    $.ajax({
                        url: "get_office.php", type: "POST",
                        data: { region_code: $(this).val() },
                        success: function (data) { $('#area').html(data); }
                    });
                });

                // Division → District
                $('#division').change(function () {
                    $.ajax({
                        url: "get_area_data.php", type: "POST",
                        data: { division_code: $(this).val() },
                        success: function (data) {
                            $('#district').html(data);
                            $('#upazilla').html('<option value="">-- Select Upazilla --</option>');
                        }
                    });
                });

                // District → Upazilla
                $('#district').change(function () {
                    $.ajax({
                        url: "get_area_data.php", type: "POST",
                        data: { district_code: $(this).val() },
                        success: function (data) { $('#upazilla').html(data); }
                    });
                });

            });
        </script>

        <?php
        include '../../include/footer.php';
    }
}
?>