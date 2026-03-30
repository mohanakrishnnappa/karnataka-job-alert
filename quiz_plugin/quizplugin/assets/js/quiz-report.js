if (!document.getElementById("wzq-report-modal")) {
} else {// 🚩 REPORT SYSTEM (SEPARATE FILE)

    let reportModal = document.getElementById("wzq-report-modal");
    let reportText = document.getElementById("wzq-report-text");
    let reportSubmit = document.getElementById("wzq-report-submit");
    let reportCancel = document.getElementById("wzq-report-cancel");
    const wrapperEl = document.querySelector(".wzq-wrapper");

    let currentReportBtn = null;
    let currentReportQ = null;

    let reportReason = document.getElementById("wzq-report-reason");
    let reportMsg = document.getElementById("wzq-report-msg");

    function showMsg(text, type = "success") {

        reportMsg.innerText = text;
        reportMsg.className = "wzq-report-msg show " + type;

    }

    // 👉 OPEN MODAL
    document.addEventListener("click", function (e) {
        if (!e.target.classList.contains("wzq-report")) return;

        const btn = e.target;
        const q = btn.closest(".wzq-question");

        currentReportBtn = btn;
        currentReportQ = q;

        // 🔁 reset form
        reportText.value = "";
        reportReason.value = "";

        // 🔁 reset message
        reportMsg.className = "wzq-report-msg";
        reportMsg.innerText = "";

        // 🔥 IMPORTANT: reset submit button
        reportSubmit.disabled = false;
        reportSubmit.innerText = "Submit";

        reportModal.classList.add("show");
    });

    // ❌ Cancel
    reportCancel.addEventListener("click", () => {
        reportModal.classList.remove("show");

        // 🔁 reset button
        reportSubmit.disabled = false;
        reportSubmit.innerText = "Submit";
    });

    // ✅ Submit
    reportSubmit.addEventListener("click", () => {

        // 🚫 prevent double click
        if (reportSubmit.disabled) return;

        reportSubmit.disabled = true;
        reportSubmit.innerText = "Submitting...";

        const reason = reportReason.value;
        const extra = reportText.value.trim();

        if (!reason) {
            showMsg("Please select issue ⚠️", "warning");

            // 🔁 re-enable if validation fails
            reportSubmit.disabled = false;
            reportSubmit.innerText = "Submit";
            return;
        }

        const issue = extra ? `${reason} - ${extra}` : reason;

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
                quiz_id: wrapperEl.dataset.quiz
            })
        })
            .then(res => res.text())
            .then(res => {

                if (res.trim() === "SUCCESS") {

                    btn.disabled = true;
                    btn.innerText = "Reported ✅";
                    btn.classList.add("wzq-reported");

                    showMsg("Reported successfully ✅", "success");

                    setTimeout(() => {
                        reportModal.classList.remove("show");
                    }, 1200);

                } else {
                    showMsg("Error: " + res, "error");

                    // 🔁 re-enable if server error
                    reportSubmit.disabled = false;
                    reportSubmit.innerText = "Submit";
                }

            })
            .catch(err => {
                console.error(err);

                // 🔁 re-enable on failure
                reportSubmit.disabled = false;
                reportSubmit.innerText = "Submit";
            });

    });
}