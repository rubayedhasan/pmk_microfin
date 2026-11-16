/**
 * ===========================================
 * Feature:: Server Request location path
 * ===========================================
 */
const serverRequestUrl = "../server/insert_members.php";

//define:: tracking member code (for tracking the member on insert / update on save)
let originalMemberCode = null;

/**
 * ===========================================
 * Feature:: Multi-step Navigation
 * ===========================================
 */
const stepNav = document.querySelector("#stepNav");
const stepButtons = document.querySelectorAll(".step-btn");
const stepContainers = document.querySelectorAll(
  `.member-admission-container > div[id^='step-']`,
);

// access the all action buttons
const prevBtn = document.querySelector("#PrevBtn");
const nextBtn = document.querySelector("#NextBtn");
const submitBtn = document.querySelector("#submitBtn");
const cancelBtn = document.querySelector("#cancelBtn");

// define the current step & isSaving (for tracking the step container and step form save)
let currentStep = 1;
let isSaving = false;

// FUNCTION:: show active step container
function showActiveStep(stepNo) {
  const targetedStep = document.querySelector(`#step-${stepNo}`);

  //   validation:: step container is exists
  if (!targetedStep) {
    return;
  }

  //   set the current step
  currentStep = stepNo;

  //   hide all step container
  stepContainers.forEach((stepCon) => {
    stepCon.style.display = "none";
  });

  //show only the current or targeted step container
  targetedStep.style.display = "block";

  //   update:: step button active state
  stepButtons.forEach((stepBtn) => {
    stepBtn.classList.toggle(
      "step-active",
      Number(stepBtn.dataset.step) === currentStep,
    );
  });

  //   disable the prev / nex button
  prevBtn.disabled = currentStep === 1;
  nextBtn.disabled = currentStep === stepContainers.length;
}

// FUNCTION:: disable/enable the buttons on request on server.
function setButtonDisable(status) {
  isSaving = status;
  submitBtn.disable = status;

  // validation:: disable button base on status
  if (status) {
    prevBtn.disabled = status;
    nextBtn.disabled = status;
  } else {
    prevBtn.disabled = currentStep === 1;
    nextBtn.disabled = currentStep === stepContainers.length;
  }
}

/**
 * ===========================================
 * Feature:: Insert / Update a step on server
 * ===========================================
 */
async function serverRequest(formData) {
  const serverResponse = await fetch(serverRequestUrl, {
    method: "POST",
    body: formData,
  });

  // define:: data to store server response
  let responseData;
  try {
    responseData = await serverResponse.json();
  } catch (e) {
    throw new Error("Invalid response return from server.");
  }

  // validation:: request success or not
  if (!responseData.success) {
    throw new Error(responseData.message || "Unable to Save From Data");
  }

  return responseData;
}

// FUNCTION:: remember the document path (after step 5 saved)
function saveDocuments(docs) {
  const docRows = document.querySelectorAll(
    ".document-main-container .document-container",
  );

  docRows.forEach((docRow, idx) => {
    const doc = docs[idx];
    if (!doc) {
      return;
    }
    docRow.querySelector("input[name='existing_path[]']").value = doc.path;

    // file is stored, so withdraw the member_doc[] inputs required attr
    const mainFileInput = docRow.querySelector("input[name='member_doc[]']");
    mainFileInput.required = false;
    mainFileInput.value = "";
  });
}

// FUNCTION:: save a step. on success return true
async function savingAStep(stepNo) {
  // define the form tag on each step
  const formStep = document.querySelector(`#form-step-${stepNo}`);

  // validation form:: if user not fill the required input then user can't submit hte form
  if (!formStep.reportValidity()) {
    return false;
  }

  //FUNCTIONALITY (step-3):: total nominee must be exactly 100
  if (stepNo === 3) {
    const totalNomineeShare = [
      ...formStep.querySelectorAll("input[name='nominee_share[]']"),
    ].reduce(
      (sumOfShare, NomShareInput) =>
        sumOfShare + Number(NomShareInput.value || 0),
      0,
    );

    // validation:: check nominee share
    if (totalNomineeShare !== 100) {
      alert(
        `The total share of all nominees must equal 100%. Current Share is ${totalNomineeShare}%`,
      );
      return false;
    }
  }

  // validation:: step 1 must complete first
  if (stepNo > 1 && !originalMemberCode) {
    alert("Complete the Member Info Form first then Others");
    return false;
  }

  // define the form data
  const formData = new FormData(formStep);
  formData.append("step", stepNo);
  if (originalMemberCode) {
    formData.append("original_member_id", originalMemberCode);
  }

  try {
    const serverdata = await serverRequest(formData);
    // set the tracking member code
    originalMemberCode = serverdata.original_member_id;
    console.log(originalMemberCode);
    if (serverdata.original_member_id) {
      document.querySelector("#orgMemCode").value = originalMemberCode;
    }

    // set the document
    if (stepNo === 5 && Array.isArray(serverdata.documents)) {
      saveDocuments(serverdata.documents);
    }

    return true;
  } catch (err) {
    console.log(err);
    alert(err.message);
    return false;
  }
}

// FUNCTIONALITY:: Next Button -> insert current step's data to database and go next step
nextBtn.addEventListener("click", async function () {
  // check saving status and if not saving disable next button
  if (isSaving) {
    return;
  }
  setButtonDisable(true);

  // done the step and enable the next button
  const stepDone = await savingAStep(currentStep);
  setButtonDisable(true);

  // go next step
  if (stepDone) {
    showActiveStep(currentStep + 1);
  }
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

// FUNCTIONALITY:: save and submit button
submitBtn.addEventListener("click", async function () {
  if (isSaving) {
    return;
  }

  // make sure every step has been saved at least once with valid data
  for (let sc = 1; stepContainers.length; sc++) {
    // steps before the current one were saved when the user clicked Next,
    // but re-saving is cheap and guarantees the latest edits are stored
    showActiveStep(sc);
    if (!(await savingAStep(sc))) {
      setButtonDisable(false);
      return;
    }
  }

  try {
    const formData2 = new FormData();
    formData2.append("step", "final");
    formData2.append("original_member_id", originalMemberCode);
    await serverRequest(formData2);
    alert("Member Admission Complete");

    // redirect page
    window.history.back();
  } catch (err) {
    alert(err.message);
  }
  setButtonDisable(false);
});

// FUNCTIONALITY:: cancel button
cancelBtn.addEventListener("click", function () {
  if (
    confirm(
      "Leave this page? Your form will be saved as a draft and can be completed later.",
    )
  ) {
    // redirect page
    window.history.back();
  }
});

//FUNCTION CALL:: initial step show
showActiveStep(1);

/**
 * ===========================================
 * STEP-1:: Basic Information
 * ===========================================
 */

// FUNCTIONALITY:: set admission date as today date
const admissionDateField = document.querySelector("#admissionDate");
admissionDateField.value = new Date().toISOString().slice(0, 10);

/**
 * FUNCTION:: validation
 */
function NameValidation(inp) {
  const nameRegex = /^[a-zA-Z\s]*$/;

  //   the value
  const nameValue = inp.value.trim();
  const nameHints = inp.parentElement.querySelector(".nameHints");

  //   validation
  if (nameValue) {
    if (!nameRegex.test(nameValue)) {
      nameHints.style.display = "block";
      nameHints.innerText =
        "Invalid: special characters & Number are not allowed.";
      inp.style.borderColor = "#e11d48";
    } else {
      nameHints.style.display = "none";
      inp.style.borderColor = "#dde3ef";
    }
  } else {
    nameHints.style.display = "none";
    inp.style.borderColor = "#dde3ef";
  }
}

/**
 *  FUNCTIONALITY:: member name validation
 */
const memberNameField = document.querySelector("#memberName");
memberNameField.addEventListener("input", function () {
  NameValidation(this);
});

/**
 *  FUNCTIONALITY:: member surname validation
 */
const memberSurnameField = document.querySelector("#memberSurname");
memberSurnameField.addEventListener("input", function () {
  NameValidation(this);
});

/**
 *  FUNCTIONALITY:: father name validation
 */
const fatherNameField = document.querySelector("#memberFather");
fatherNameField.addEventListener("input", function () {
  NameValidation(this);
});

/**
 *  FUNCTIONALITY:: mother name validation
 */
const motherNameField = document.querySelector("#memberMother");
motherNameField.addEventListener("input", function () {
  NameValidation(this);
});

/**
 *  FUNCTIONALITY:: SHOW Spouse Name field base on Marital status
 */
const maritalStatusField = document.querySelector("#memberMaritalStatus");
const spouseParentField = document.querySelector("#spouseParentField");
const spouseNameField = document.querySelector("#memberSpouseName");
maritalStatusField.addEventListener("change", function () {
  const fieldValue = this.value;
  if (fieldValue === "Married" || fieldValue === "Widow") {
    spouseParentField.style.display = "block";
    spouseNameField.required = true;
  } else {
    spouseParentField.style.display = "none";
    spouseNameField.required = false;
  }
});

/**
 *  FUNCTIONALITY:: spouse name validation
 */
spouseNameField.addEventListener("input", function () {
  NameValidation(this);
});

/**
 *  FUNCTIONALITY:: DATE OF BIRTH input field
 */
const dobField = document.querySelector("#memberDOB");
const ageField = document.querySelector("#memberAge");

// limited the date of birth input value (Member age only 18 to 60 is allowed)
function calculateDateRange(min, max) {
  // today
  const today = new Date();
  const limitedDate = new Date(
    `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`,
  );

  //   min date (max age 60 year)
  const minAgeDate = new Date(limitedDate);
  minAgeDate.setFullYear(minAgeDate.getFullYear() - max);

  //   max date (min age 18 year)
  const maxAgeDate = new Date(limitedDate);
  maxAgeDate.setFullYear(maxAgeDate.getFullYear() - min);

  //   Date Formate
  const dateFormate = (date) => {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");

    return `${yyyy}-${mm}-${dd}`;
  };

  return {
    min: dateFormate(minAgeDate),
    max: dateFormate(maxAgeDate),
  };
}

// set the date range
const dobRange = calculateDateRange(18, 60);
dobField.min = dobRange.min;
dobField.max = dobRange.max;

// show picker on focus the input
dobField.addEventListener("focus", function () {
  this.showPicker();
});

// Calculate the Age (From: DOB To: Today)
dobField.addEventListener("input", function () {
  // today
  const today = new Date();

  // current value
  let birthDate = new Date(this.value);

  let age = today.getFullYear() - birthDate.getFullYear();

  //   check whether the birthday has occurred this year
  const hasBirthdayPassed =
    today.getMonth() > birthDate.getMonth() ||
    (today.getMonth() === birthDate.getMonth() &&
      today.getDate() >= birthDate.getDate());

  if (!hasBirthdayPassed) {
    age--;
  }

  //   set the age in age field value
  ageField.value = age;
});

/**
 *  FUNCTIONALITY:: validation of Mobile number
 */
const mobileField = document.querySelector("#memberMobile");
const mobileHintsField = document.querySelector("#mobileHints");
mobileField.addEventListener("input", function () {
  // validation code
  const mobileRegex = /^\+?(88)?01[3-9]\d{8}$/;

  //   only number allowed
  this.value = this.value.replace(/[^0-9]/g, "");

  //   the mobile number
  const number = this.value;

  //   validation
  if (number) {
    if (!mobileRegex.test(number)) {
      mobileHintsField.style.display = "block";
      mobileHintsField.innerText = "Wrong Number Format.";
      mobileField.style.borderColor = "#e11d48";
    } else {
      mobileHintsField.style.display = "none";
      mobileField.style.borderColor = "#dde3ef";
    }
  } else {
    mobileHintsField.style.display = "none";
    mobileField.style.borderColor = "#dde3ef";
  }
});

/**
 *  FUNCTIONALITY::Smart ID validation
 */
const smartIdField = document.querySelector("#memberSmartId");
const sidHints = document.querySelector("#sidHints");
const nidField = document.querySelector("#memberNID");
const nidHints = document.querySelector("#nidHints");
smartIdField.addEventListener("input", function () {
  const smartRegex = /^\d{10}$/;

  // Allow numbers only
  this.value = this.value.replace(/\D/g, "");

  const smartId = this.value.trim().replace(/[\s-]/g, "");

  //   validation
  if (smartId) {
    if (!smartRegex.test(smartId)) {
      sidHints.style.display = "block";
      sidHints.innerText = "Invalid: Smart Id Must 10 Digit.";
      smartIdField.style.borderColor = "#e11d48";
    } else {
      sidHints.style.display = "none";
      smartIdField.style.borderColor = "#dde3ef";

      nidField.required = false;
    }
  } else {
    sidHints.style.display = "none";
    smartIdField.style.borderColor = "#dde3ef";
  }
});

/**
 *  FUNCTIONALITY::NID validation
 */
nidField.addEventListener("input", function () {
  const nidRegex = /^\d{17}$/;

  // Allow numbers only
  this.value = this.value.replace(/\D/g, "");

  const nid = this.value.trim().replace(/[\s-]/g, "");

  //   validation
  if (nid) {
    if (!nidRegex.test(nid)) {
      nidHints.style.display = "block";
      nidHints.innerText = "Invalid: NID Must 17 digit.";
      nidField.style.borderColor = "#e11d48";
    } else {
      nidHints.style.display = "none";
      nidField.style.borderColor = "#dde3ef";
    }
  } else {
    nidHints.style.display = "none";
    nidField.style.borderColor = "#dde3ef";

    smartIdField.required = false;
  }
});

/**
 * ===========================================
 * STEP-2:: Address Information
 * ===========================================
 */
// get all input data (NID Address)
const nid_division_field = document.querySelector("#MemberNIDDivision");
const nid_district_field = document.querySelector("#MemberNIDDistrict");
const nid_upazila_field = document.querySelector("#MemberNIDUpazila");
const nid_union_field = document.querySelector("#MemberNIDUnion");
const nid_village_field = document.querySelector("#memberNIDVillage");
const nid_house_field = document.querySelector("#memberNIDHouseNo");

// get all input data (Present Address)
const present_division_field = document.querySelector("#MemberPresentDivision");
const present_district_field = document.querySelector("#MemberPresentDistrict");
const present_upazila_field = document.querySelector("#MemberPresentUpazila");
const present_union_field = document.querySelector("#MemberPresentUnion");
const present_village_field = document.querySelector("#memberPresentVillage");
const present_house_field = document.querySelector("#memberPresentHouseNo");

// get all input data (Permanent Address)
const permanent_division_field = document.querySelector(
  "#MemberPermanentDivision",
);
const permanent_district_field = document.querySelector(
  "#MemberPermanentDistrict",
);
const permanent_upazila_field = document.querySelector(
  "#MemberPermanentUpazila",
);
const permanent_union_field = document.querySelector("#MemberPermanentUnion");
const permanent_village_field = document.querySelector(
  "#memberPermanentVillage",
);
const permanent_house_field = document.querySelector("#memberPermanentHouseNo");

// same ase checkbox element
const presentAsNID = document.querySelector("#presentAsNid");
const permanentAsNID = document.querySelector("#permanentAsNid");

// CHECKBOX:: same as NID for present address

presentAsNID.addEventListener("change", function () {
  if (this.checked) {
    present_division_field.value = nid_division_field.value;
    present_district_field.value = nid_district_field.value;
    present_upazila_field.value = nid_upazila_field.value;
    present_union_field.value = nid_union_field.value;
    present_village_field.value = nid_village_field.value;
    present_house_field.value = nid_house_field.value;
  } else {
    present_division_field.value = "";
    present_district_field.value = "";
    present_upazila_field.value = "";
    present_union_field.value = "";
    present_village_field.value = "";
    present_house_field.value = "";
  }
});

// CHECKBOX:: same as NID for permanent address
permanentAsNID.addEventListener("change", function () {
  if (this.checked) {
    permanent_division_field.value = nid_division_field.value;
    permanent_district_field.value = nid_district_field.value;
    permanent_upazila_field.value = nid_upazila_field.value;
    permanent_union_field.value = nid_union_field.value;
    permanent_village_field.value = nid_village_field.value;
    permanent_house_field.value = nid_house_field.value;
  } else {
    permanent_division_field.value = "";
    permanent_district_field.value = "";
    permanent_upazila_field.value = "";
    permanent_union_field.value = "";
    permanent_village_field.value = "";
    permanent_house_field.value = "";
  }
});

/**
 *  FUNCTIONALITY:: validation of Mobile number family member
 */
const mobileFieldFamily = document.querySelector("#memberFamilyContact");
const mobileHintsFieldFamily = document.querySelector("#mobileHintsFamily");
mobileFieldFamily.addEventListener("input", function () {
  // validation code
  const mobileRegex = /^\+?(88)?01[3-9]\d{8}$/;

  //   only number allowed
  this.value = this.value.replace(/[^0-9]/g, "");

  //   the mobile number
  const number = this.value;

  //   validation
  if (number) {
    if (!mobileRegex.test(number)) {
      mobileHintsFieldFamily.style.display = "block";
      mobileHintsFieldFamily.innerText = "Wrong Number Format.";
      mobileFieldFamily.style.borderColor = "#e11d48";
    } else {
      mobileHintsFieldFamily.style.display = "none";
      mobileFieldFamily.style.borderColor = "#dde3ef";
    }
  } else {
    mobileHintsFieldFamily.style.display = "none";
    mobileFieldFamily.style.borderColor = "#dde3ef";
  }
});

/**
 * ===========================================
 * STEP-3:: Nominee & Reference
 * ===========================================
 */

// FUNCTIONALITY:: ADD NOMINEE
const addNomineeBtn = document.querySelector("#add-nominee");
const nomineeTableBody = document.querySelector("#nominee-table-body");
const nomineeRowTemplate = document.querySelector("#nominee-row-template");

// FUNCTION:: clone the template to tbody
function newNomineeRow() {
  const nomineeRow =
    nomineeRowTemplate.content.firstElementChild.cloneNode(true);
  nomineeTableBody.appendChild(nomineeRow);
}

// initially show a row
newNomineeRow();

//CLICK EVENT FUNCTION:: ADD Row
addNomineeBtn.addEventListener("click", () => newNomineeRow());

// FUNCTIONALITY:: REMOVE Unusual Nominee Row
nomineeTableBody.addEventListener("click", function (e) {
  const rowRemoveBtn = e.target.closest(".nominee-remove-btn");

  if (!rowRemoveBtn) {
    return;
  }

  const specificRow = rowRemoveBtn.closest("tr");
  specificRow.remove();
});

// FUNCTIONALITY:: ADD Reference
const addReferenceBtn = document.querySelector("#add-reference");
const referenceTableBody = document.querySelector("#reference-table-body");
const referenceRowTemplate = document.querySelector("#reference-row-template");

// FUNCTION:: clone the template to tbody
function newReferenceRow() {
  const referenceRow =
    referenceRowTemplate.content.firstElementChild.cloneNode(true);
  referenceTableBody.appendChild(referenceRow);
}

// initially show a row
newReferenceRow();

//CLICK EVENT FUNCTION:: ADD Row
addReferenceBtn.addEventListener("click", () => newReferenceRow());

// FUNCTIONALITY:: REMOVE Unusual Nominee Row
referenceTableBody.addEventListener("click", function (e) {
  const rowRemoveBtn = e.target.closest(".reference-remove-btn");

  if (!rowRemoveBtn) {
    return;
  }

  const specificRow = rowRemoveBtn.closest("tr");
  specificRow.remove();
});

// FUNCTIONALITY:: nominee name validation
const nomineeNameFields = document.querySelectorAll(
  "input[name^='nominee_name[]']",
);
nomineeNameFields.forEach(function (nomineeNameField) {
  nomineeNameField.addEventListener("input", function () {
    NameValidation(this);
  });
});

// FUNCTIONALITY:: nominee mobile validation
document.addEventListener("input", function (e) {
  if (e.target.matches('input[name="nominee_mobile[]"]')) {
    const inp = e.target;
    const mobileRegex = /^(?:01[3-9]\d{8}|8801[3-9]\d{8})$/;

    const hintsField = inp.parentElement.querySelector(".nominee-mobile-hints");

    inp.value = inp.value.replace(/[^0-9]/g, "");

    if (inp.value && !mobileRegex.test(inp.value)) {
      hintsField.style.display = "block";
      hintsField.innerText = "Wrong Number Format.";
      inp.style.borderColor = "#e11d48";
    } else {
      hintsField.style.display = "none";
      inp.style.borderColor = "#dde3ef";
    }
  }
});

// FUNCTIONALITY:: reference name validation
const referenceNameFields = document.querySelectorAll(
  "input[name^='reference_name[]']",
);
referenceNameFields.forEach(function (referenceNameField) {
  referenceNameField.addEventListener("input", function () {
    NameValidation(this);
  });
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

// FUNCTION:: to clone the doc input row
function newDocRow() {
  const docRow = documentRowTemplate.content.cloneNode(true);
  documentMainContainer.appendChild(docRow);
}

// initial show a row
newDocRow();

// EVENT FUNCTION:: add rew doc row
addDocBtn.addEventListener("click", () => {
  newDocRow();
});

// FUNCTIONALITY:: remove row
documentMainContainer.addEventListener("click", function (e) {
  const removeBtn = e.target.closest(".doc-remove-btn");
  const specificRow = removeBtn.closest(".document-container");
  specificRow.remove();
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

// customized select2 functionality
// document.addEventListener("DOMContentLoaded", () => {
//   const selects = document.querySelectorAll(".custom-select-container select");

//   selects.forEach((select) => {
//     new Select2(select, {
//       dropdownCssClass: "custom-dropdown-container",
//       width: "100%",
//     });
//   });
// });
