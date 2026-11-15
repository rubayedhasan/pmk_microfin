/**
 * ===========================================
 * Feature:: Server Request location path
 * ===========================================
 */
const serverRequestUrl = "./server/insert_members.php";

// tracking member id (set after step 1 is inserted, used for every update afterwards)
let originalMemberCode = null;

/**
 * ===========================================
 * Feature:: Multi-step Navigation
 * ===========================================
 */
const stepButtons = document.querySelectorAll(".step-btn");
const stepContainers = document.querySelectorAll(
  `.member-admission-container > div[id^='step-']`,
);
const TOTAL_STEPS = stepContainers.length;

// access all action buttons
const prevBtn = document.querySelector("#PrevBtn");
const nextBtn = document.querySelector("#NextBtn");
const submitBtn = document.querySelector("#submitBtn");
const cancelBtn = document.querySelector("#cancelBtn");

let currentStep = 1;
let isSaving = false;

/**
 * ===========================================
 * Feature:: Loading spinner (full screen overlay + button spinner)
 * CSS lives in member_admission_spinner.css
 * ===========================================
 */
const loaderEl = document.createElement("div");
loaderEl.className = "admission-loader";
loaderEl.setAttribute("role", "status");
loaderEl.setAttribute("aria-live", "polite");
loaderEl.innerHTML = `
  <div class="admission-loader__box">
    <span class="admission-spinner"></span>
    <p class="admission-loader__text">Saving...</p>
  </div>`;
document.body.appendChild(loaderEl);
const loaderText = loaderEl.querySelector(".admission-loader__text");

function showLoader(text) {
  loaderText.textContent = text || "Saving...";
  loaderEl.classList.add("show");
}

function hideLoader() {
  loaderEl.classList.remove("show");
}

// small spinner inside the button that was clicked
function setBtnLoading(btn, loading) {
  if (!btn) return;
  btn.classList.toggle("is-loading", loading);
  const existing = btn.querySelector(".btn-spinner");
  if (loading && !existing) {
    const spinner = document.createElement("span");
    spinner.className = "btn-spinner";
    btn.prepend(spinner);
  } else if (!loading && existing) {
    existing.remove();
  }
}

// ONE place that decides which buttons are enabled
function updateButtons() {
  prevBtn.disabled = isSaving || currentStep === 1;
  nextBtn.disabled = isSaving || currentStep === TOTAL_STEPS;
  // submit is only available on the last step
  submitBtn.disabled = isSaving || currentStep !== TOTAL_STEPS;
  cancelBtn.disabled = isSaving;
}

// start / stop the "saving" state. ALWAYS call setSaving(false) in a finally block.
function setSaving(status, label, activeBtn) {
  isSaving = status;
  updateButtons();
  if (status) {
    showLoader(label);
  } else {
    hideLoader();
  }
  setBtnLoading(activeBtn, status);
}

// FUNCTION:: show active step container
function showActiveStep(stepNo) {
  const targetedStep = document.querySelector(`#step-${stepNo}`);
  if (!targetedStep) {
    return;
  }

  currentStep = stepNo;

  stepContainers.forEach((stepCon) => {
    stepCon.style.display = "none";
  });
  targetedStep.style.display = "block";

  stepButtons.forEach((stepBtn) => {
    stepBtn.classList.toggle(
      "step-active",
      Number(stepBtn.dataset.step) === currentStep,
    );
  });

  updateButtons();
  if (stepNo === 1) {
    submitBtn.style.display = "none";
  } else if (stepNo !== 1 && stepNo !== 5) {
    prevBtn.style.display = "";
    submitBtn.style.display = "none";
  } else {
    prevBtn.style.display = "";
    submitBtn.style.display = "";
  }
}

/**
 * ===========================================
 * Feature:: Validate + Insert / Update a step
 * ===========================================
 */
async function serverRequest(formData) {
  const serverResponse = await fetch(serverRequestUrl, {
    method: "POST",
    body: formData,
  });

  // read as text first, so we can SEE what the server really sent
  const rawText = await serverResponse.text();

  let responseData;
  try {
    responseData = JSON.parse(rawText);
  } catch (e) {
    console.error(
      `Server did not return JSON (HTTP ${serverResponse.status}). Raw response:\n`,
      rawText,
    );
    throw new Error(
      `Invalid response from server (HTTP ${serverResponse.status}). Press F12 and open the Console to see the raw output.`,
    );
  }

  if (!responseData.success) {
    if (responseData.debug) {
      console.error("Server debug:", responseData.debug);
    }
    throw new Error(responseData.message || "Unable to save form data.");
  }

  return responseData;
}

// FUNCTION:: remember the document path (after step 5 saved)
function saveDocuments(docs) {
  // NOTE: #document-main-container is an ID (it was written as a class before)
  const docRows = document.querySelectorAll(
    "#document-main-container .document-container",
  );

  docRows.forEach((docRow, idx) => {
    const doc = docs[idx];
    if (!doc) {
      return;
    }
    docRow.querySelector("input[name='existing_path[]']").value = doc.filePath;

    // file is stored, so the file input is no longer required
    const mainFileInput = docRow.querySelector("input[name='member_doc[]']");
    mainFileInput.required = false;
    mainFileInput.value = "";
  });
}

// FUNCTION:: browser validation (shows the native "Please fill out this field" popup).
// The step MUST be visible, otherwise the browser cannot show the popup.
function validateStep(stepNo) {
  const formStep = document.querySelector(`#form-step-${stepNo}`);

  if (!formStep.reportValidity()) {
    return false;
  }

  if (stepNo === 3) {
    const shareInputs = formStep.querySelectorAll(
      "input[name='nominee_share[]']",
    );

    if (shareInputs.length === 0) {
      alert("At least one nominee is required.");
      return false;
    }

    const totalNomineeShare = [...shareInputs].reduce(
      (sum, inp) => sum + Number(inp.value || 0),
      0,
    );

    if (totalNomineeShare !== 100) {
      alert(
        `The total share of all nominees must equal 100%. Current share is ${totalNomineeShare}%`,
      );
      return false;
    }
  }

  if (
    stepNo === 5 &&
    formStep.querySelectorAll("input[name='member_doc[]']").length === 0
  ) {
    alert("At least one document is required.");
    return false;
  }

  return true;
}

// FUNCTION:: send a step to the server. Throws an Error if anything fails.
async function saveStep(stepNo) {
  if (stepNo > 1 && !originalMemberCode) {
    throw new Error("Complete the Member Info form first, then the others.");
  }

  const formStep = document.querySelector(`#form-step-${stepNo}`);
  const formData = new FormData(formStep);
  formData.append("step", stepNo);
  if (originalMemberCode) {
    formData.append("original_member_id", originalMemberCode);
  }

  const serverData = await serverRequest(formData);

  if (serverData.original_member_id) {
    originalMemberCode = serverData.original_member_id;
    document.querySelector("#orgMemCode").value = originalMemberCode;
  }

  if (stepNo === 5 && Array.isArray(serverData.documents)) {
    saveDocuments(serverData.documents);
  }
}

// FUNCTIONALITY:: Next Button -> validate, save current step, go next
nextBtn.addEventListener("click", async function () {
  if (isSaving) {
    return;
  }

  // 1) validate FIRST (before the spinner) so the browser popup is visible
  if (!validateStep(currentStep)) {
    return;
  }

  // 2) save
  let errorMessage = "";
  setSaving(true, `Saving step ${currentStep}...`, nextBtn);
  try {
    await saveStep(currentStep);
  } catch (err) {
    console.error(err);
    errorMessage = err.message;
  } finally {
    // always runs: buttons come back even if the request failed
    setSaving(false, "", nextBtn);
  }

  // 3) result
  if (errorMessage) {
    alert(errorMessage);
    return;
  }
  showActiveStep(currentStep + 1);
});

// FUNCTIONALITY:: Prev Button
prevBtn.addEventListener("click", function () {
  if (isSaving) {
    return;
  }
  showActiveStep(currentStep - 1);
});

// FUNCTIONALITY:: stop form default behavior
document.querySelectorAll(".step-form").forEach((form) => {
  form.addEventListener("submit", (event) => event.preventDefault());
});

// FUNCTIONALITY:: save and submit button (enabled on the last step only)
submitBtn.addEventListener("click", async function () {
  if (isSaving) {
    return;
  }

  // 1) validate every step. Stops on the first invalid step and shows its popup.
  for (let sc = 1; sc <= TOTAL_STEPS; sc++) {
    showActiveStep(sc);
    if (!validateStep(sc)) {
      return;
    }
  }

  // 2) save every step again (guarantees the latest edits are stored), then finalize
  let failedStep = null;
  let errorMessage = "";
  let completed = false;

  setSaving(true, "Saving...", submitBtn);
  try {
    for (let sc = 1; sc <= TOTAL_STEPS; sc++) {
      failedStep = sc;
      loaderText.textContent = `Saving step ${sc} of ${TOTAL_STEPS}...`;
      await saveStep(sc);
    }
    failedStep = null;

    loaderText.textContent = "Finalizing admission...";
    const finalData = new FormData();
    finalData.append("step", "final");
    finalData.append("original_member_id", originalMemberCode);
    await serverRequest(finalData);

    completed = true;
  } catch (err) {
    console.error(err);
    errorMessage = err.message;
  } finally {
    setSaving(false, "", submitBtn);
  }

  // 3) result
  if (!completed) {
    if (failedStep) {
      showActiveStep(failedStep); // take the user to the step that failed
    }
    alert(errorMessage);
    return;
  }

  alert("Member Admission Complete");
  window.history.back();
});

// FUNCTIONALITY:: cancel button
cancelBtn.addEventListener("click", function () {
  if (
    confirm(
      "Leave this page? Your form will be saved as a draft and can be completed later.",
    )
  ) {
    window.history.back();
  }
});

//FUNCTION CALL:: initial step show
showActiveStep(1);

/**
 * ===========================================
 * Shared validation helpers
 * setCustomValidity() makes the browser popup block the step
 * (reportValidity) when the format is wrong, not only when empty.
 * ===========================================
 */
function markField(inp, hintEl, message) {
  const invalid = Boolean(message);
  inp.setCustomValidity(message || "");
  inp.style.borderColor = invalid ? "#e11d48" : "#dde3ef";
  if (hintEl) {
    hintEl.style.display = invalid ? "block" : "none";
    hintEl.innerText = message || "";
  }
}

// FUNCTION:: name validation
function NameValidation(inp) {
  const nameRegex = /^[a-zA-Z\s]*$/;
  const nameValue = inp.value.trim();
  const nameHints = inp.parentElement.querySelector(".nameHints");

  markField(
    inp,
    nameHints,
    nameValue && !nameRegex.test(nameValue)
      ? "Invalid: special characters & numbers are not allowed."
      : "",
  );
}

// FUNCTION:: Bangladeshi mobile number validation (digits only)
function MobileValidation(inp, hintEl) {
  const mobileRegex = /^(?:88)?01[3-9]\d{8}$/;
  inp.value = inp.value.replace(/[^0-9]/g, "");

  markField(
    inp,
    hintEl,
    inp.value && !mobileRegex.test(inp.value)
      ? "Wrong number format. e.g: 017XXXXXXXX"
      : "",
  );
}

// FUNCTION:: today as yyyy-mm-dd in the LOCAL time zone
function toLocalISODate(date) {
  const yyyy = date.getFullYear();
  const mm = String(date.getMonth() + 1).padStart(2, "0");
  const dd = String(date.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
}

/**
 * ===========================================
 * STEP-1:: Basic Information
 * ===========================================
 */

// FUNCTIONALITY:: set admission date as today date
document.querySelector("#admissionDate").value = toLocalISODate(new Date());

// name fields
["#memberName", "#memberSurname", "#memberFather", "#memberMother"].forEach(
  (selector) => {
    document.querySelector(selector).addEventListener("input", function () {
      NameValidation(this);
    });
  },
);

/**
 *  FUNCTIONALITY:: SHOW Spouse Name field base on Marital status
 */
const maritalStatusField = document.querySelector("#memberMaritalStatus");
const spouseParentField = document.querySelector("#spouseParentField");
const spouseNameField = document.querySelector("#memberSpouseName");
maritalStatusField.addEventListener("change", function () {
  const needSpouse = this.value === "Married" || this.value === "Widow";
  spouseParentField.style.display = needSpouse ? "block" : "none";
  spouseNameField.required = needSpouse;
  if (!needSpouse) {
    spouseNameField.value = "";
    markField(
      spouseNameField,
      spouseParentField.querySelector(".nameHints"),
      "",
    );
  }
});

spouseNameField.addEventListener("input", function () {
  NameValidation(this);
});

/**
 *  FUNCTIONALITY:: DATE OF BIRTH input field (age 18 to 60 only)
 */
const dobField = document.querySelector("#memberDOB");
const ageField = document.querySelector("#memberAge");

function calculateDateRange(minAge, maxAge) {
  const today = new Date();
  const oldest = new Date(
    today.getFullYear() - maxAge,
    today.getMonth(),
    today.getDate(),
  );
  const youngest = new Date(
    today.getFullYear() - minAge,
    today.getMonth(),
    today.getDate(),
  );
  return { min: toLocalISODate(oldest), max: toLocalISODate(youngest) };
}

const dobRange = calculateDateRange(18, 60);
dobField.min = dobRange.min;
dobField.max = dobRange.max;

dobField.addEventListener("focus", function () {
  this.showPicker();
});

dobField.addEventListener("input", function () {
  if (!this.value) {
    ageField.value = "";
    return;
  }

  const today = new Date();
  const birthDate = new Date(this.value);
  let age = today.getFullYear() - birthDate.getFullYear();

  const hasBirthdayPassed =
    today.getMonth() > birthDate.getMonth() ||
    (today.getMonth() === birthDate.getMonth() &&
      today.getDate() >= birthDate.getDate());

  if (!hasBirthdayPassed) {
    age--;
  }

  ageField.value = age;
});

/**
 *  FUNCTIONALITY:: validation of Mobile number
 */
const mobileField = document.querySelector("#memberMobile");
mobileField.addEventListener("input", function () {
  MobileValidation(this, document.querySelector("#mobileHints"));
});

/**
 *  FUNCTIONALITY:: Smart ID / NID  (at least ONE of them is required)
 */
const smartIdField = document.querySelector("#memberSmartId");
const sidHints = document.querySelector("#sidHints");
const nidField = document.querySelector("#memberNID");
const nidHints = document.querySelector("#nidHints");

function syncIdRequirement() {
  const hasSmart = smartIdField.value.trim() !== "";
  const hasNid = nidField.value.trim() !== "";
  // both are required until one of them is filled
  smartIdField.required = !hasNid;
  nidField.required = !hasSmart;
}

smartIdField.addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, "");
  markField(
    this,
    sidHints,
    this.value && !/^\d{10}$/.test(this.value)
      ? "Invalid: Smart ID must be 10 digits."
      : "",
  );
  syncIdRequirement();
});

nidField.addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, "");
  markField(
    this,
    nidHints,
    this.value && !/^\d{17}$/.test(this.value)
      ? "Invalid: NID must be 17 digits."
      : "",
  );
  syncIdRequirement();
});

/**
 * ===========================================
 * STEP-2:: Address Information
 * ===========================================
 */
const nidAddressFields = [
  document.querySelector("#MemberNIDDivision"),
  document.querySelector("#MemberNIDDistrict"),
  document.querySelector("#MemberNIDUpazila"),
  document.querySelector("#MemberNIDUnion"),
  document.querySelector("#memberNIDVillage"),
  document.querySelector("#memberNIDHouseNo"),
];

const presentAddressFields = [
  document.querySelector("#MemberPresentDivision"),
  document.querySelector("#MemberPresentDistrict"),
  document.querySelector("#MemberPresentUpazila"),
  document.querySelector("#MemberPresentUnion"),
  document.querySelector("#memberPresentVillage"),
  document.querySelector("#memberPresentHouseNo"),
];

const permanentAddressFields = [
  document.querySelector("#MemberPermanentDivision"),
  document.querySelector("#MemberPermanentDistrict"),
  document.querySelector("#MemberPermanentUpazila"),
  document.querySelector("#MemberPermanentUnion"),
  document.querySelector("#memberPermanentVillage"),
  document.querySelector("#memberPermanentHouseNo"),
];

// copy NID address into the target address (or clear it)
function copyNidAddress(targetFields, checked) {
  targetFields.forEach((field, i) => {
    field.value = checked ? nidAddressFields[i].value : "";
  });
}

document.querySelector("#presentAsNid").addEventListener("change", function () {
  copyNidAddress(presentAddressFields, this.checked);
});

document
  .querySelector("#permanentAsNid")
  .addEventListener("change", function () {
    copyNidAddress(permanentAddressFields, this.checked);
  });

/**
 *  FUNCTIONALITY:: validation of Mobile number family member
 */
document
  .querySelector("#memberPermanentContact")
  .addEventListener("input", function () {
    MobileValidation(this, document.querySelector("#mobileHintsPerCon"));
  });

/**
 * ===========================================
 * STEP-3:: Nominee & Reference
 * ===========================================
 */
const addNomineeBtn = document.querySelector("#add-nominee");
const nomineeTableBody = document.querySelector("#nominee-table-body");
const nomineeRowTemplate = document.querySelector("#nominee-row-template");

function newNomineeRow() {
  const nomineeRow =
    nomineeRowTemplate.content.firstElementChild.cloneNode(true);
  nomineeTableBody.appendChild(nomineeRow);
}

// initially show a row
newNomineeRow();

addNomineeBtn.addEventListener("click", () => newNomineeRow());

nomineeTableBody.addEventListener("click", function (e) {
  const rowRemoveBtn = e.target.closest(".nominee-remove-btn");
  if (!rowRemoveBtn) {
    return;
  }
  rowRemoveBtn.closest("tr").remove();
});

const addReferenceBtn = document.querySelector("#add-reference");
const referenceTableBody = document.querySelector("#reference-table-body");
const referenceRowTemplate = document.querySelector("#reference-row-template");

function newReferenceRow() {
  const referenceRow =
    referenceRowTemplate.content.firstElementChild.cloneNode(true);
  referenceTableBody.appendChild(referenceRow);
}

// initially show a row
newReferenceRow();

addReferenceBtn.addEventListener("click", () => newReferenceRow());

referenceTableBody.addEventListener("click", function (e) {
  const rowRemoveBtn = e.target.closest(".reference-remove-btn");
  if (!rowRemoveBtn) {
    return;
  }
  rowRemoveBtn.closest("tr").remove();
});

// FUNCTIONALITY:: nominee / reference validation.
// Event delegation, so rows added later are validated too.
document.addEventListener("input", function (e) {
  const inp = e.target;

  if (
    inp.matches("input[name='nominee_name[]'], input[name='reference_name[]']")
  ) {
    NameValidation(inp);
  }

  if (inp.matches("input[name='nominee_mobile[]']")) {
    MobileValidation(
      inp,
      inp.parentElement.querySelector(".nominee-mobile-hints"),
    );
  }
});

/**
 * ===========================================
 * STEP-5::Document
 * ===========================================
 */
const documentMainContainer = document.querySelector(
  "#document-main-container",
);
const documentRowTemplate = document.querySelector("#document-row-template");
const addDocBtn = document.querySelector("#add-doc-btn");

function newDocRow() {
  const docRow = documentRowTemplate.content.cloneNode(true);
  documentMainContainer.appendChild(docRow);
}

// initial show a row
newDocRow();

addDocBtn.addEventListener("click", () => newDocRow());

// FUNCTIONALITY:: remove row
documentMainContainer.addEventListener("click", function (e) {
  const removeBtn = e.target.closest(".doc-remove-btn");
  if (!removeBtn) {
    return; // click was somewhere else inside the container
  }
  removeBtn.closest(".document-container").remove();
});

// FUNCTIONALITY:: doc preview
documentMainContainer.addEventListener("change", function (e) {
  if (!e.target.matches("input[name='member_doc[]']")) {
    return;
  }

  const file = e.target.files[0];
  const img = e.target
    .closest(".document-container")
    .querySelector(".member-doc-preview img");
  if (file && file.type.startsWith("image/")) {
    img.src = URL.createObjectURL(file);
  }
});
