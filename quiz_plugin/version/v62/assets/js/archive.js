document.addEventListener("click", function (e) {

    const btn = e.target.closest(".wzq-filter-btn");
    if (!btn) return;

    const cat = btn.dataset.cat;

    console.log("CLICK:", cat);

    document.querySelectorAll(".wzq-filter-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    const container = document.getElementById("wzq-quiz-container");

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
        </div>
        `;
        }

        return html;
    }

    fetch(wzq_ajax.url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "wzq_filter_quiz",
            cat: cat
        })
    })
        .then(res => res.text())
        .then(html => {
            console.log("RESPONSE:", html);
            container.innerHTML = html;
        })
        .catch(err => console.error(err));

});