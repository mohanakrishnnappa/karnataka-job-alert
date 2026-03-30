document.addEventListener("DOMContentLoaded", function () {

    const wrapper = document.querySelector(".wzq-wrapper");
    if (!wrapper) return;

    const quizId = wrapper.dataset.quiz;
    const key = "quiz_cert_" + quizId;

    // 🔥 CLEAR on refresh
    sessionStorage.removeItem(key);

});

document.addEventListener("click", async function (e) {

    const wrapper = document.querySelector(".wzq-wrapper");
    if (!wrapper) return;

    const popup = document.getElementById("wzq-cert-popup");
    const nameInput = document.getElementById("wzq-cert-name");

    // 🎓 DOWNLOAD CLICK
    const downloadBtn = e.target.closest("#wzq-download-cert");

    if (downloadBtn) {

        const quizId = wrapper.dataset.quiz;
        const key = "quiz_cert_" + quizId;

        const data = JSON.parse(sessionStorage.getItem(key));

        // ✅ Already generated → download directly
        if (data && data.generated && data.image) {

            const link = document.createElement("a");
            link.download = "certificate.png";
            link.href = data.image;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            return; // 🔥 IMPORTANT (stop popup)
        }

        // ❗ First time → show popup
        if (popup) popup.style.display = "flex";
    }

    // ❌ CANCEL
    const cancelBtn = e.target.closest("#wzq-cert-cancel");
    if (cancelBtn) {
        if (popup) popup.style.display = "none";
    }

    // ✅ SAVE
    const saveBtn = e.target.closest("#wzq-cert-save");

    if (saveBtn) {

        const name = nameInput?.value.trim();
        if (!name) return alert("Enter name");

        const quizId = wrapper.dataset.quiz;
        const key = "quiz_cert_" + quizId;

        const scoreText = document.querySelector(".wzq-score-text")?.innerText || "0/0";
        const parts = scoreText.split("/").map(s => s.trim());
        const score = parts[0];
        const total = parts[1];

        const quizTitle = document.querySelector(".wzq-question-text")?.innerText || "Quiz";

        generateCertificate(name, score, total, quizTitle, function (image) {

            sessionStorage.setItem(key, JSON.stringify({
                generated: true,
                name: name,
                image: image
            }));

        });

        if (popup) popup.style.display = "none";

        const shareBtn = document.getElementById("wzq-share-cert");
        if (shareBtn) shareBtn.style.display = "inline-block";
    }

    // 📲 SHARE
    const shareBtn = e.target.closest("#wzq-share-cert");
    if (shareBtn) {

        const text = `🎉 I completed a quiz!\nTry here 👉 ${wzq_cert.site_url}`;

        if (navigator.share && window.generatedCert) {
            const blob = await (await fetch(window.generatedCert)).blob();

            const file = new File([blob], "certificate.png", { type: "image/png" });

            navigator.share({
                files: [file],
                title: "My Certificate",
                text: text
            });
        } else {
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`);
        }
    }

    // 🎨 GENERATE CERTIFICATE
    function generateCertificate(name, score, total, quizTitle, callback) {

        const canvas = document.createElement("canvas");
        canvas.width = 1400;
        canvas.height = 1000;

        const ctx = canvas.getContext("2d");

        // 🎨 WHITE BACKGROUND
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // 🖼️ LOGO WATERMARK
        const logo = new Image();
        logo.src = wzq_cert.logo;

        let done = false;

        function drawAll() {

            if (done) return;
            done = true;

            // draw watermark only if loaded
            if (logo.complete && logo.naturalWidth !== 0) {

                ctx.globalAlpha = 0.06;

                const size = 512;

                ctx.drawImage(
                    logo,
                    (canvas.width - size) / 2,
                    (canvas.height - size) / 2,
                    size,
                    size
                );

                ctx.globalAlpha = 1;
            }

            // 🟡 DOUBLE BORDER
            ctx.strokeStyle = "#d4af37";
            ctx.lineWidth = 8;
            ctx.strokeRect(30, 30, 1340, 940);

            ctx.strokeStyle = "#000";
            ctx.lineWidth = 2;
            ctx.strokeRect(50, 50, 1300, 900);

            ctx.textAlign = "center";

            // 🏆 TITLE
            ctx.font = "bold 60px Arial";
            ctx.fillStyle = "#222";
            ctx.fillText("CERTIFICATE", 700, 180);

            ctx.font = "28px Arial";
            ctx.fillStyle = "#555";
            ctx.fillText("OF ACHIEVEMENT", 700, 230);

            // 📜 Subtitle
            ctx.font = "24px Arial";
            ctx.fillStyle = "#444";
            ctx.fillText("This is proudly presented to", 700, 320);

            // 🧑 NAME
            ctx.font = "bold 56px Verdana";
            ctx.fillStyle = "#ef2c6d";

            const upperName = name.toUpperCase();
            ctx.fillText(upperName, 700, 420);

            // 🔝 TOP LINE
            ctx.beginPath();
            ctx.moveTo(450, 360);
            ctx.lineTo(950, 360);

            // 🔻 BOTTOM LINE
            ctx.moveTo(450, 440);
            ctx.lineTo(950, 440);

            ctx.strokeStyle = "#d4af37";
            ctx.lineWidth = 2;
            ctx.stroke();

            // 📚 Description
            ctx.font = "26px Arial";
            ctx.fillStyle = "#333";
            ctx.fillText("for successfully completing the quiz", 700, 520);

            ctx.font = "bold 32px Arial";
            ctx.fillStyle = "#000";
            ctx.fillText(quizTitle, 700, 580);

            // 🎯 Score box
            ctx.fillStyle = "#fff";
            ctx.fillRect(550, 630, 300, 70);

            ctx.strokeStyle = "#d4af37";
            ctx.strokeRect(550, 630, 300, 70);

            ctx.font = "bold 28px Arial";
            ctx.fillStyle = "#000";
            ctx.fillText(`Score: ${score}/${total}`, 700, 675);

            // 📅 Date
            ctx.font = "20px Arial";
            ctx.fillStyle = "#444";
            ctx.fillText(new Date().toLocaleDateString(), 700, 750);

            // 🟡 GOLD SEAL
            ctx.beginPath();
            ctx.arc(1100, 780, 70, 0, Math.PI * 2);
            ctx.fillStyle = "#d4af37";
            ctx.fill();

            ctx.fillStyle = "#000";
            ctx.font = "bold 16px Arial";
            ctx.fillText("CERTIFIED", 1100, 785);

            // ✍️ SIGNATURE
            const signatureImg = new Image();
            signatureImg.src = wzq_cert.signature;

            signatureImg.onload = function () {

                const centerX = 300;
                const lineY = 800;

                // 📏 LINE
                ctx.beginPath();
                ctx.moveTo(centerX - 120, lineY);
                ctx.lineTo(centerX + 120, lineY);
                ctx.strokeStyle = "#000";
                ctx.lineWidth = 1;
                ctx.stroke();

                // 🖊️ SIGNATURE (220x40 aligned)
                const sigWidth = 220;
                const sigHeight = 40;

                const sigY = lineY - sigHeight - 5;

                ctx.drawImage(
                    signatureImg,
                    centerX - sigWidth / 2,
                    sigY,
                    sigWidth,
                    sigHeight
                );

                // 👤 NAME
                ctx.font = "italic 26px Cursive";
                ctx.fillStyle = "#000";
                ctx.fillText("Mohana Krishnnappa", centerX, 830);

                // 🏷️ ROLE
                ctx.font = "18px Arial";
                ctx.fillStyle = "#444";
                ctx.fillText("Founder", centerX, 860);

                finish();
            };

            signatureImg.onerror = finish;

            function finish() {

                const image = canvas.toDataURL("image/png");

                const link = document.createElement("a");
                link.download = "certificate.png";
                link.href = image;

                document.body.appendChild(link);
                setTimeout(() => link.click(), 100);
                document.body.removeChild(link);

                window.generatedCert = image;

                callback(image);
            }
        }

        // load handling
        logo.onload = drawAll;
        logo.onerror = drawAll;

        // fallback safety
        setTimeout(drawAll, 300);
    }

});