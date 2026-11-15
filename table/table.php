<?php
session_start();
include('../include/db_connect.php');
error_reporting(0);

if (strlen($_SESSION['login']) == 0) {
    header('location:../index.php');
} else {

    $PageID = 'P032';
    $ModulePage = 'P013';
    $ModuleID = 002;
    include('../module_admin/user_query.php');

    if (!in_array($ModulePage, $RolModule)) {
        header('location:../include/user_permission_require.php');
    } elseif (!in_array($PageID, $RoleUpdateViw)) {
        header('location:../include/user_permission_require.php');
    } else {

        include '../include/sidebar.php';
        include '../include/header.php';
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <!-- jQuery (must load first, before any other plugin) -->
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

            <!-- SweetAlert2 (used by empl_list.js for delete confirmation) -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <link rel="stylesheet" href="css/table.css">
        </head>

        <body>
            <div id="mainContent" class="main-content">
                <div class="container-fluid">

                    <!-- ── Page Header ─────────────────────────────────────── -->
                    <div class="page-header-card">
                        <div>
                            <h3>
                                <i class="bi bi-people-fill" style="color:#128E69"></i>
                                Employee List
                            </h3>
                            <p>View, search &amp; manage employee records</p>
                        </div>
                        <a href="employee_reg.php" class="btn-new-emp">
                            <i class="bi bi-person-plus-fill"></i> New Employee
                        </a>
                    </div>

                    <!-- ── Flash message ───────────────────────────────────── -->
                    <?php if (isset($_SESSION['status'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert"
                            style="border-radius:12px; margin-bottom:16px;">
                            <?= htmlspecialchars($_SESSION['status']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['status']); endif; ?>

                    <!-- ── Search / Filter ─────────────────────────────────── -->
                    <form id="multiSearchForm">
                        <div class="filter-card">
                            <div class="filter-grid">

                                <input type="text" id="s_employee_id" placeholder="Employee ID">

                                <input type="text" id="s_employee_name" placeholder="Employee Name">

                                <select id="s_department">
                                    <option value="">All Departments</option>
                                </select>

                                <select id="s_designation">
                                    <option value="">All Designations</option>
                                </select>

                                <select id="s_branch">
                                    <option value="">All Branches</option>
                                </select>

                                <select id="s_type">
                                    <option value="">All Types</option>
                                </select>

                                <div>
                                    <label
                                        style="font-size:11px;font-weight:600;color:#9aa3bc;display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px">Join
                                        From</label>
                                    <input type="date" id="s_join_from">
                                </div>

                                <div>
                                    <label
                                        style="font-size:11px;font-weight:600;color:#9aa3bc;display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px">Join
                                        To</label>
                                    <input type="date" id="s_join_to">
                                </div>

                                <div class="filter-actions">
                                    <button type="submit" class="btn-search">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                    <button type="button" id="resetSearch" class="btn-reset">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>

                    <!-- ── Employee Table ──────────────────────────────────── -->
                    <div class="table-card">

                        <div class="table-overlay" aria-live="polite"></div>

                        <div class="table-responsive">
                            <table class="employee-table">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Joining Date</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Work Station</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- populated by empl_list.js -->
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- ── Pagination ──────────────────────────────────────── -->
                    <div class="table-pagination"><button class="btn btn-primary">1</button></div>

                </div><!-- /container-fluid -->
            </div><!-- /main-content -->

        </body>

        </html>






        <!-- Employee list logic -->
        <script src="empl_list.js" defer></script>

        <?php include '../include/footer.php'; ?>

        <?php
    } // end permission check
} // end session check
?>