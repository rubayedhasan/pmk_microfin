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

// access the prev/next buttons
const prevBtn = document.querySelector("#PrevBtn");
const nextBtn = document.querySelector("#NextBtn");

// define the current step
let currentStep = 1;

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

  //   show only the current or targeted step container
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

// FUNCTIONALITY:: Next Button
nextBtn.addEventListener("click", function () {
  showActiveStep(currentStep + 1);
});

// FUNCTIONALITY:: Prev Button
prevBtn.addEventListener("click", function () {
  showActiveStep(currentStep - 1);
});

// initial step show
showActiveStep(1);

/**
 * ===========================================
 * STEP-1:: Basic Information
 * ===========================================
 */

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
maritalStatusField.addEventListener("change", function () {
  const fieldValue = this.value;
  console.log(fieldValue);
  if (fieldValue === "Married" || fieldValue === "Widow") {
    spouseParentField.style.display = "block";
  } else {
    spouseParentField.style.display = "none";
  }
});

/**
 *  FUNCTIONALITY:: spouse name validation
 */
const spouseNameField = document.querySelector("#memberSpouseName");
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
    }
  } else {
    sidHints.style.display = "none";
    smartIdField.style.borderColor = "#dde3ef";
  }
});

/**
 *  FUNCTIONALITY::NID validation
 */
const nidField = document.querySelector("#memberNID");
const nidHints = document.querySelector("#nidHints");
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
