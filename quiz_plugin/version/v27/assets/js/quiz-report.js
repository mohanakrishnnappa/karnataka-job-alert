// 🚩 REPORT SYSTEM (SEPARATE FILE)

let reportModal = document.getElementById("wzq-report-modal");
let reportText = document.getElementById("wzq-report-text");
let reportSubmit = document.getElementById("wzq-report-submit");
let reportCancel = document.getElementById("wzq-report-cancel");

let currentReportBtn = null;
let currentReportQ = null;

// 👉 OPEN MODAL
document.addEventListener("click", function (e) {

    if (!e.target.classList.contains("wzq-report")) return;

    const btn = e.target;
    const q = btn.closest(".wzq-question");

    currentReportBtn = btn;
    currentReportQ = q;

    reportText.value = "";
    reportModal.classList.add("show");
});

// ❌ Cancel
reportCancel.addEventListener("click", () => {
    reportModal.classList.remove("show");
});

// ✅ Submit
reportSubmit.addEventListener("click", () => {

    const issue = reportText.value.trim();
    if (!issue) return alert("Please enter issue");

    const q = currentReportQ;
    const btn = currentReportBtn;

    const questionText = q.querySelector(".wzq-question-text").cloneNode(true);
    questionText.querySelector(".wzq-q-number")?.remove();
    const cleanText = questionText.innerText;

    fetch(wzq_ajax.url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "wzq_report_question",
            question: cleanText,
            issue: issue,
            quiz_id: wrapper.dataset.quiz
        })
    })
        .then(res => res.text())
        .then(res => {

            if (res.trim() === "SUCCESS") {

                btn.disabled = true;
                btn.innerText = "Reported ✅";
                btn.classList.add("wzq-reported");

                reportModal.classList.remove("show");

            } else {
                alert("Error: " + res);
            }

        })
        .catch(err => {
            console.error(err);
            alert("Error reporting ❌");
        });

});