document.addEventListener("click", function (e) {

    const btn = e.target.closest(".wzq-filter-btn");
    if (!btn) return;

    const cat = btn.dataset.cat || "";

    // Active state
    document.querySelectorAll(".wzq-filter-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    const container = document.getElementById("wzq-quiz-container");

    // Skeleton loader
    container.innerHTML = getSkeletonHTML(6);

    function getSkeletonHTML(count = 6) {
        let html = "";
        for (let i = 0; i < count; i++) {
            html += `
            <div class="wzq-skeleton-card">
                <div class="wzq-skeleton wzq-skeleton-title"></div>
                <div class="wzq-skeleton wzq-skeleton-meta"></div>
                <div class="wzq-skeleton wzq-skeleton-meta"></div>
                <div class="wzq-skeleton wzq-skeleton-btn"></div>
            </div>`;
        }
        return html;
    }

    // ✅ AJAX call
    fetch(wzq_ajax.url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        },
        body: new URLSearchParams({
            action: "wzq_filter_quiz",
            cat: cat
        })
    })
        .then(res => {
            if (!res.ok) {
                throw new Error("HTTP error: " + res.status);
            }
            return res.text();
        })
        .then(html => {
            // Handle empty / 0 response
            if (!html || html.trim() === "0") {
                container.innerHTML = "<p>No quizzes found</p>";
                return;
            }

            container.innerHTML = html;
        })
        .catch(err => {
            console.error("AJAX ERROR:", err);

            container.innerHTML = "<p>Something went wrong</p>";
        });

});