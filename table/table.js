/* ================================
   empl_list.js — No Selectize dep
   Custom searchable dropdowns built
   in pure JS. Zero jQuery required.
================================ */

document.addEventListener("DOMContentLoaded", () => {

  /* ── DOM refs ───────────────────────────────────── */
  const table = document.querySelector(".employee-table") || document.querySelector(".table");
  if (!table) { console.error("empl_list.js: table element not found"); return; }
  const tbody = table.querySelector("tbody");
  const paginationContainer = document.querySelector(".table-pagination");
  const overlay = document.querySelector(".table-overlay");
  const searchForm = document.getElementById("multiSearchForm");
  const resetBtn = document.getElementById("resetSearch");

  /* ── State ──────────────────────────────────────── */
  let allRows = [];
  let filteredRows = [];
  let rawData = [];
  let currentPage = 1;
  const PER_PAGE = 10;

  /* ── Custom dropdown registry ───────────────────── */
  const dropdowns = {};   // id → { getValue, setValue, setOptions }

  /* ================================================
     INJECT STYLES
  ================================================ */
  if (!document.getElementById("empl-styles")) {
    const s = document.createElement("style");
    s.id = "empl-styles";
    s.textContent = `
      /* ── Searchable dropdown ── */
      .sd-wrap {
        position: relative;
        user-select: none;
      }  
      .sd-wrap .sd-display {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 40px;
        border: 1.5px solid #dde3ef;
        border-radius: 9px;
        padding: 0 12px;
        font-size: 13px;
        color: #1a2b4a;
        background: #f8f9fc;
        cursor: pointer;
        transition: border-color .18s, box-shadow .18s;
        white-space: nowrap;
        overflow: hidden;
      }
      .sd-wrap .sd-display:focus,
      .sd-wrap.open .sd-display {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        outline: none;
      }
      .sd-wrap .sd-display .sd-text {
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
      }
      .sd-wrap .sd-display .sd-arrow {
        margin-left: 6px;
        font-size: 10px;
        color: #9aa3bc;
        flex-shrink: 0;
        transition: transform .18s;
      }
      .sd-wrap.open .sd-display .sd-arrow {
        transform: rotate(180deg);
      }
      .sd-panel {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1.5px solid #dde3ef;
        border-radius: 9px;
        box-shadow: 0 6px 20px rgba(0,0,0,.1);
        z-index: 9999;
        overflow: hidden;
      }
      .sd-wrap.open .sd-panel {
        display: block;
      }
      .sd-panel input.sd-search {
        width: 100%;
        height: 36px;
        border: none;
        border-bottom: 1.5px solid #f0f2f7;
        padding: 0 12px;
        font-size: 13px;
        outline: none;
        background: #fafbff;
        box-sizing: border-box;
      }
      .sd-options {
        max-height: 200px;
        overflow-y: auto;
      }
      .sd-option {
        padding: 9px 12px;
        font-size: 13px;
        color: #2d3a55;
        cursor: pointer;
        transition: background .1s;
      }
      .sd-option:hover,
      .sd-option.focused {
        background: #f0f4ff;
        color: #2563eb;
      }
      .sd-option.selected {
        background: #eef2ff;
        color: #2563eb;
        font-weight: 600;
      }
      .sd-option.placeholder {
        color: #9aa3bc;
      }
      .sd-empty {
        padding: 10px 12px;
        font-size: 13px;
        color: #9aa3bc;
        text-align: center;
      }

      /* ── Pagination ── */
      .table-pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin-top: 14px;
        justify-content: center;
      }
      .table-pagination button {
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border: 1.5px solid #dde3ef;
        border-radius: 8px;
        background: #fff;
        color: #4b5a7a;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        pointer-events: auto !important;
      }
      .table-pagination button:hover:not(.disabled):not(.active) {
        background: #f0f4ff;
        border-color: #2563eb;
        color: #2563eb;
      }
      .table-pagination button.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        cursor: default;
      }
      .table-pagination button.disabled {
        opacity: .4;
        cursor: not-allowed;
        pointer-events: none !important;
      }
      .table-pagination .page-input {
        width: 72px;
        height: 34px;
        border: 1.5px solid #dde3ef;
        border-radius: 8px;
        padding: 0 8px;
        font-size: 13px;
        text-align: center;
        margin-left: 4px;
      }
      .table-pagination .pag-info {
        font-size: 12px;
        color: #9aa3bc;
        margin-left: 8px;
      }
        

      /* ── Overlay ── */
      .table-overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 20px;
        font-size: 14px;
        color: #888;
      }

      .btn-details {
        background: rgba(165, 102, 241, .1);
        color: gray;
        border: 1px solid rgba(99, 102, 241, .4);
      }

      .btn-edit {
        background: rgba(234, 179, 8, .2);
        color: green;
        border: 1px solid rgba(234, 179, 8, .3);
      }

     .btn-delete {
        background: rgba(239, 68, 68, .2);
        color: red;
        border: 1px solid rgba(239, 68, 68, .3);
      }
      .table-overlay.hidden { display: none !important; }
    `;

    document.head.appendChild(s);


  }

  /* ================================================
     CUSTOM SEARCHABLE DROPDOWN
     Replaces a <select> with a custom panel.
     onChange(value) fires when selection changes.
  ================================================ */
  function makeDropdown(selectId, placeholder, onChange) {
    const native = document.getElementById(selectId);
    if (!native) return null;

    // Hide native select but keep it in DOM for form reset
    native.style.display = "none";

    let currentValue = "";
    let options = [];   // [{value, label}]

    /* Build wrapper */
    const wrap = document.createElement("div");
    wrap.className = "sd-wrap";
    wrap.dataset.id = selectId;
    native.parentNode.insertBefore(wrap, native);

    const display = document.createElement("div");
    display.className = "sd-display";
    display.tabIndex = 0;
    display.innerHTML = `<span class="sd-text">${placeholder}</span><span class="sd-arrow">▼</span>`;
    wrap.appendChild(display);

    const panel = document.createElement("div");
    panel.className = "sd-panel";
    panel.innerHTML = `<input class="sd-search" type="text" placeholder="Search...">
                       <div class="sd-options"></div>`;
    wrap.appendChild(panel);

    const searchInput = panel.querySelector(".sd-search");
    const optList = panel.querySelector(".sd-options");

    /* Render options into panel */
    function renderOptions(filter) {
      optList.innerHTML = "";

      // Always show "All" / placeholder option
      const allOpt = document.createElement("div");
      allOpt.className = "sd-option placeholder" + (currentValue === "" ? " selected" : "");
      allOpt.textContent = placeholder;
      allOpt.dataset.value = "";
      optList.appendChild(allOpt);

      const q = (filter || "").toLowerCase();
      const filtered = q
        ? options.filter(o => o.label.toLowerCase().includes(q))
        : options;

      if (filtered.length === 0 && q) {
        const empty = document.createElement("div");
        empty.className = "sd-empty";
        empty.textContent = "No results";
        optList.appendChild(empty);
      }

      filtered.forEach(o => {
        const div = document.createElement("div");
        div.className = "sd-option" + (o.value === currentValue ? " selected" : "");
        div.textContent = o.label;
        div.dataset.value = o.value;
        optList.appendChild(div);
      });
    }

    /* Open/close */
    function open() {
      // Close any other open dropdowns first
      document.querySelectorAll(".sd-wrap.open").forEach(w => {
        if (w !== wrap) w.classList.remove("open");
      });
      wrap.classList.add("open");
      searchInput.value = "";
      renderOptions("");
      searchInput.focus();
    }
    function close() {
      wrap.classList.remove("open");
    }

    /* Display label for current value */
    function updateDisplay() {
      const sdText = display.querySelector(".sd-text");
      if (currentValue === "") {
        sdText.textContent = placeholder;
        sdText.style.color = "#9aa3bc";
      } else {
        const found = options.find(o => o.value === currentValue);
        sdText.textContent = found ? found.label : placeholder;
        sdText.style.color = "#1a2b4a";
      }
    }

    /* Select an option */
    function select(value) {
      currentValue = value;
      native.value = value;  // keep native in sync
      updateDisplay();
      close();
      if (onChange) onChange(value);
    }

    /* Events */
    display.addEventListener("click", () => wrap.classList.contains("open") ? close() : open());
    display.addEventListener("keydown", e => {
      if (e.key === "Enter" || e.key === " ") { e.preventDefault(); open(); }
      if (e.key === "Escape") close();
    });

    searchInput.addEventListener("input", () => renderOptions(searchInput.value));
    searchInput.addEventListener("keydown", e => {
      if (e.key === "Escape") close();
    });

    optList.addEventListener("click", e => {
      const opt = e.target.closest(".sd-option");
      if (opt) select(opt.dataset.value);
    });

    // Close on outside click
    document.addEventListener("click", e => {
      if (!wrap.contains(e.target)) close();
    });

    /* Public API */
    const api = {
      getValue() { return currentValue; },
      setValue(v, silent) {
        currentValue = v;
        native.value = v;
        updateDisplay();
        if (!silent && onChange) onChange(v);
      },
      setOptions(items, clearFirst) {
        // items: [{value, label}]
        if (clearFirst) options = [];
        // Avoid duplicates
        const existing = new Set(options.map(o => o.value));
        items.forEach(i => { if (!existing.has(i.value)) options.push(i); });
        updateDisplay();
      },
      clearOptions() { options = []; updateDisplay(); },
      reset() { this.setValue("", true); searchInput.value = ""; }
    };

    dropdowns[selectId] = api;
    return api;
  }

  /* ── Helper: get value from dropdown or native select ── */
  function getVal(id) {
    return dropdowns[id] ? dropdowns[id].getValue() : (document.getElementById(id)?.value || "");
  }

  /* ================================================
     OVERLAY
  ================================================ */
  function showOverlay(msg) {
    if (!overlay) return;
    overlay.textContent = msg;
    overlay.classList.remove("hidden");
  }
  function hideOverlay() {
    if (!overlay) return;
    overlay.classList.add("hidden");
  }

  /* ================================================
     FETCH DATA
  ================================================ */
  async function fetchData() {
    showOverlay("Loading...");
    try {
      const res = await fetch("empl_get_data.php");
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); }
      catch (e) {
        showOverlay("Server error: " + text.substring(0, 200));
        return;
      }
      if (!Array.isArray(data) || data.length === 0) {
        showOverlay("No records found.");
        return;
      }
      rawData = data;
      buildRows(data);
      initDropdowns(data);
    } catch (err) {
      showOverlay("Network error — check empl_get_data.php");
      console.error(err);
    }
  }

  /* ================================================
     BUILD ROWS
  ================================================ */
  function buildRows(data) {
    allRows = data.map(emp => {
      const tr = document.createElement("tr");
      tr.dataset.id = emp.employee_id;
      tr.dataset.empId = (emp.employee_id || "").toLowerCase();
      tr.dataset.name = (emp.employee_name || "").toLowerCase();
      tr.dataset.join = emp.emp_joining_date || "";
      tr.dataset.dept = (emp.DepName || "").toLowerCase();
      tr.dataset.desg = (emp.DesName + "-(" + emp.designation + ")").toLowerCase();
      tr.dataset.branch = (emp.BranchName + "-(" + emp.work_station + ")").toLowerCase();
      tr.dataset.type = (emp.emploee_type || "").toLowerCase();

      tr.innerHTML = `
        <td>${emp.employee_id}</td>
        <td>${emp.employee_name}</td>
        <td>${emp.emp_joining_date}</td>
        <td>${emp.DepName}</td>
        <td>${emp.DesName}-(${emp.designation})</td>
        <td>${emp.BranchName}-(${emp.work_station})</td>
        <td>${emp.emploee_type}</td>
        <td>
          <button class="btn-details" data-id="${emp.employee_id}"><i class="fa fa-eye"></i></button>  
          <button class="btn-edit"    data-id="${emp.employee_id}"><i class="fa fa-pencil"></i></button>
          <button class="btn-delete"  data-id="${emp.employee_id}"><i class="fa fa-trash"></i></button> 
        </td>`;
      return tr;
    });

    filteredRows = [...allRows];
    renderPage(1);
  }

  /* ================================================
     INIT DROPDOWNS  (called once after data loads)
  ================================================ */
  function initDropdowns(data) {
    const unique = key => [...new Set(data.map(d => d[key]).filter(Boolean))].sort();
    const toOpts = arr => arr.map(v => ({ value: v, label: v }));

    const depts = unique("DepName");
    const desigs = [...new Set(data.map(d => `${d.DesName}-(${d.designation})`).filter(Boolean))].sort();
    const branches = [...new Set(data.map(d => `${d.BranchName}-(${d.work_station})`).filter(Boolean))].sort();
    const types = unique("emploee_type");

    // Create custom dropdowns
    const ddDept = makeDropdown("s_department", "All Department", onDeptChange);
    const ddDesg = makeDropdown("s_designation", "All Designation", null);
    makeDropdown("s_branch", "All Branch", null);
    makeDropdown("s_type", "All Type", null);

    // Load initial options
    dropdowns["s_department"]?.setOptions(toOpts(depts), false);
    dropdowns["s_designation"]?.setOptions(toOpts(desigs), false);
    dropdowns["s_branch"]?.setOptions(toOpts(branches), false);
    dropdowns["s_type"]?.setOptions(toOpts(types), false);

    /* Department → Designation cascade */
    function onDeptChange(val) {
      const lc = (val || "").toLowerCase();
      const relevant = lc
        ? rawData.filter(d => (d.DepName || "").toLowerCase() === lc)
        : rawData;

      const filtered = [...new Set(
        relevant.map(d => `${d.DesName}-(${d.designation})`).filter(Boolean)
      )].sort();

      const dd = dropdowns["s_designation"];
      if (dd) {
        dd.clearOptions();
        dd.setOptions(filtered.map(v => ({ value: v, label: v })), false);
        dd.setValue("", true);
      }
    }
  }

  /* ================================================
     SEARCH FORM
  ================================================ */
  searchForm.addEventListener("submit", e => {
    e.preventDefault();
    applyFilters();
  });

  resetBtn.addEventListener("click", () => {
    // Reset text inputs
    document.getElementById("s_employee_id").value = "";
    document.getElementById("s_employee_name").value = "";
    document.getElementById("s_join_from").value = "";
    document.getElementById("s_join_to").value = "";

    // Reset all custom dropdowns
    Object.values(dropdowns).forEach(dd => dd.reset());

    // Restore full designation list after dept reset
    const allDesigs = [...new Set(
      rawData.map(d => `${d.DesName}-(${d.designation})`).filter(Boolean)
    )].sort();
    const dd = dropdowns["s_designation"];
    if (dd) {
      dd.clearOptions();
      dd.setOptions(allDesigs.map(v => ({ value: v, label: v })), false);
    }

    filteredRows = [...allRows];
    renderPage(1);
  });

  /* ================================================
     APPLY FILTERS
  ================================================ */
  function applyFilters() {
    const empId = document.getElementById("s_employee_id").value.trim().toLowerCase();
    const empName = document.getElementById("s_employee_name").value.trim().toLowerCase();
    const dept = (getVal("s_department") || "").toLowerCase();
    const desg = (getVal("s_designation") || "").toLowerCase();
    const branch = (getVal("s_branch") || "").toLowerCase();
    const type = (getVal("s_type") || "").toLowerCase();
    const from = document.getElementById("s_join_from").value;
    const to = document.getElementById("s_join_to").value;

    filteredRows = allRows.filter(tr => {
      if (empId && !tr.dataset.empId.includes(empId)) return false;
      if (empName && !tr.dataset.name.includes(empName)) return false;
      if (dept && tr.dataset.dept !== dept) return false;
      if (desg && tr.dataset.desg !== desg) return false;
      if (branch && tr.dataset.branch !== branch) return false;
      if (type && tr.dataset.type !== type) return false;
      if (from && tr.dataset.join < from) return false;
      if (to && tr.dataset.join > to) return false;
      return true;
    });

    renderPage(1);
  }

  /* ================================================
     RENDER PAGE
  ================================================ */
  function renderPage(page) {
    currentPage = page;
    tbody.innerHTML = "";

    const start = (page - 1) * PER_PAGE;
    const slice = filteredRows.slice(start, start + PER_PAGE);

    if (slice.length === 0) {
      showOverlay("No data found.");
    } else {
      hideOverlay();
      slice.forEach(tr => tbody.appendChild(tr));
    }
    renderPagination();
  }

  /* ================================================
     PAGINATION
  ================================================ */
  function renderPagination() {
    paginationContainer.innerHTML = "";
    const total = Math.ceil(filteredRows.length / PER_PAGE);
    if (total <= 1) {
      if (filteredRows.length > 0) {
        const info = document.createElement("span");
        info.className = "pag-info";
        info.textContent = `${filteredRows.length} record${filteredRows.length !== 1 ? "s" : ""}`;
        paginationContainer.appendChild(info);
      }
      return;
    }

    function mkBtn(label, targetPage, disabled) {
      const b = document.createElement("button");
      b.textContent = label;
      if (disabled) {
        b.classList.add("disabled");
      } else {
        b.addEventListener("click", () => renderPage(targetPage));
      }
      paginationContainer.appendChild(b);
    }

    mkBtn("«", 1, currentPage === 1);
    mkBtn("‹", currentPage - 1, currentPage === 1);

    const maxVis = 7;
    let s = Math.max(1, currentPage - Math.floor(maxVis / 2));
    let e = Math.min(total, s + maxVis - 1);
    if (e - s < maxVis - 1) s = Math.max(1, e - maxVis + 1);

    for (let i = s; i <= e; i++) {
      const b = document.createElement("button");
      b.textContent = i;
      if (i === currentPage) b.classList.add("active");
      else b.addEventListener("click", () => renderPage(i));
      paginationContainer.appendChild(b);
    }

    mkBtn("›", currentPage + 1, currentPage === total);
    mkBtn("»", total, currentPage === total);

    // Go-to page input
    const inp = document.createElement("input");
    inp.type = "number";
    inp.min = 1;
    inp.max = total;
    inp.placeholder = "Go…";
    inp.className = "page-input";
    inp.addEventListener("keydown", e => {
      if (e.key !== "Enter") return;
      const p = parseInt(inp.value);
      if (p >= 1 && p <= total) renderPage(p);
    });
    paginationContainer.appendChild(inp);

    // Info
    const info = document.createElement("span");
    info.className = "pag-info";
    const s2 = (currentPage - 1) * PER_PAGE + 1;
    const e2 = Math.min(currentPage * PER_PAGE, filteredRows.length);
    info.textContent = `${s2}–${e2} of ${filteredRows.length}`;
    paginationContainer.appendChild(info);
  }

  /* ================================================
     ACTION BUTTONS — delegated
  ================================================ */
  tbody.addEventListener("click", e => {
    const edit = e.target.closest(".btn-edit");
    const del = e.target.closest(".btn-delete");
    const detail = e.target.closest(".btn-details");

    if (edit) {
      const id = edit.dataset.id;
      Swal.fire({
        title: "Edit Employee", text: `Edit Employee ID: ${id}?`, icon: "question",
        showCancelButton: true, confirmButtonText: "Yes, Edit",
      }).then(r => { if (r.isConfirmed) window.location.href = `employee_edit.php?id=${id}`; });
    }

    if (del) {
      const id = del.dataset.id;
      Swal.fire({
        title: "Are you sure?", text: `Employee ID ${id} will be deleted permanently!`,
        icon: "warning", showCancelButton: true,
        confirmButtonColor: "#d33", confirmButtonText: "Yes, Delete!",
      }).then(r => {
        if (!r.isConfirmed) return;

        const formData = new FormData();
        formData.append('employee_id', id);

        fetch('employee_delete.php', {
          method: 'POST',
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              Swal.fire({ icon: "success", title: "Deleted!", timer: 1500, showConfirmButton: false });
              allRows = allRows.filter(tr => tr.dataset.id !== id);
              filteredRows = filteredRows.filter(tr => tr.dataset.id !== id);
              renderPage(currentPage);
            } else {
              Swal.fire("Error!", data.message || "Delete failed.", "error");
            }
          })
          .catch(() => Swal.fire("Error!", "Could not reach server.", "error"));
      });
    }

    if (detail) {
      window.location.href = `empl_details.php?id=${detail.dataset.id}`;
    }
  });

  /* ── Init ─────────────────────────────────────── */
  fetchData();
});