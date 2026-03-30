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

        let name = nameInput?.value.trim();
        if (!name) return alert("Enter name");

        // 🔒 LIMIT NAME LENGTH (35 chars)
        if (name.length > 35) {
            name = name.substring(0, 35);
        }

        const quizId = wrapper.dataset.quiz;
        const key = "quiz_cert_" + quizId;

        const scoreText = document.querySelector(".wzq-score-text")?.innerText || "0/0";
        const parts = scoreText.split("/").map(s => s.trim());
        const score = parseInt(parts[0]);
        const total = parseInt(parts[1]);

        // 🎯 Calculate percentage
        const percentage = total > 0 ? Math.round((score / total) * 100) : 0;

        const quizTitle = document.querySelector(".wzq-question-text")?.innerText || "Quiz";

        generateCertificate(name, percentage, quizTitle, function (image) {

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
    function generateCertificate(name, percentage, quizTitle, callback) {

        const canvas = document.createElement("canvas");
        canvas.width = 1400;
        canvas.height = 1000;

        const ctx = canvas.getContext("2d");

        function fitName(ctx, text, maxWidth, initialFontSize) {
            let fontSize = initialFontSize;

            do {
                ctx.font = `bold ${fontSize}px Georgia`;
                fontSize--;
            } while (ctx.measureText(text).width > maxWidth && fontSize > 28);

            return fontSize;
        }

        // 🎨 WHITE BACKGROUND
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // 🖼️ LOGO
        const logo = new Image();
        logo.src = wzq_cert.logo;

        function drawAll() {

            // 🖼️ WATERMARK
            if (logo.complete && logo.naturalWidth !== 0) {

                ctx.globalAlpha = 0.06;

                const size = 620;

                ctx.drawImage(
                    logo,
                    (canvas.width - size) / 2,
                    (canvas.height - size) / 2,
                    size,
                    size
                );

                ctx.globalAlpha = 1;
            }

            // 🟡 BORDER
            ctx.strokeStyle = "#d4af37";
            ctx.lineWidth = 8;
            ctx.strokeRect(30, 30, 1340, 940);

            ctx.strokeStyle = "#000";
            ctx.lineWidth = 2;
            ctx.strokeRect(50, 50, 1300, 900);

            ctx.textAlign = "center";

            // 🏆 TITLE
            ctx.font = "bold 64px Georgia";
            ctx.fillStyle = "#d6336c";
            ctx.fillText("CERTIFICATE", 700, 180);

            ctx.font = "28px Arial";
            ctx.fillStyle = "#555";
            ctx.fillText("OF ACHIEVEMENT", 700, 230);

            // 📜 Subtitle
            ctx.font = "24px Arial";
            ctx.fillStyle = "#444";
            ctx.fillText("This is proudly presented to", 700, 320);

            // 🧑 NAME
            const upperName = name.toUpperCase();
            const fontSize = fitName(ctx, upperName, 900, 56);
            ctx.font = `bold ${fontSize}px Georgia`;
            ctx.fillStyle = "#ef2c6d";

            const nameY = 430;
            ctx.fillText(upperName, canvas.width / 2, nameY);

            // 📏 Lines
            const textWidth = ctx.measureText(upperName).width;
            const centerX = canvas.width / 2;

            ctx.beginPath();
            ctx.moveTo(centerX - textWidth / 2 - 20, nameY - 50);
            ctx.lineTo(centerX + textWidth / 2 + 20, nameY - 50);

            ctx.moveTo(centerX - textWidth / 2 - 20, nameY + 20);
            ctx.lineTo(centerX + textWidth / 2 + 20, nameY + 20);

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

            // 🎯 Score
            ctx.fillStyle = "#f1f3f5";
            ctx.strokeStyle = "#d4af37";
            ctx.strokeRect(540, 630, 320, 70);

            ctx.font = "bold 28px Arial";
            ctx.fillStyle = "#000";
            ctx.fillText(`Performance: ${percentage}%`, 700, 675);

            // 📄 Disclaimer
            ctx.font = "16px Arial";
            ctx.fillStyle = "#777";

            wrapText(
                ctx,
                "This certificate is issued for participation and performance in an online quiz.",
                700,
                760,
                900,
                22
            );

            // 🌐 Domain
            const domain = wzq_cert.site_host || "yourwebsite.com";
            ctx.fillStyle = "#555";
            ctx.fillText(`Issued by: ${domain}`, 700, 800);

            // 📅 Date
            ctx.font = "20px Arial";
            ctx.fillStyle = "#444";
            ctx.fillText(`Date: ${new Date().toLocaleDateString()}`, 700, 845);

            // 🔥 LOAD IMAGES (seal + signature)
            const sealImg = new Image();
            sealImg.src = wzq_cert.certifiedseal;

            const signatureImg = new Image();
            signatureImg.src = wzq_cert.signature;

            let sealLoaded = false;
            let signLoaded = false;

            function tryFinish() {

                if (!sealLoaded || !signLoaded) return;

                // 🟡 SEAL
                const sealSize = 125;

                ctx.shadowColor = "rgba(0,0,0,0.2)";
                ctx.shadowBlur = 10;

                ctx.drawImage(
                    sealImg,
                    1100 - sealSize / 2,
                    780 - sealSize / 2,
                    sealSize,
                    sealSize
                );

                ctx.shadowBlur = 0;

                // ✍️ SIGNATURE
                const signX = 300;
                const lineY = 800;

                ctx.beginPath();
                ctx.moveTo(signX - 120, lineY);
                ctx.lineTo(signX + 120, lineY);
                ctx.strokeStyle = "#000";
                ctx.stroke();

                ctx.drawImage(
                    signatureImg,
                    signX - 110,
                    lineY - 45,
                    220,
                    40
                );

                ctx.font = "italic 26px Cursive";
                ctx.fillStyle = "#000";
                ctx.fillText("Mohana Krishnnappa", signX, 830);

                ctx.font = "18px Arial";
                ctx.fillStyle = "#444";
                ctx.fillText("Founder & Quiz Author", signX, 860);

                finish();
            }

            sealImg.onload = () => { sealLoaded = true; tryFinish(); };
            sealImg.onerror = () => { sealLoaded = true; tryFinish(); };

            signatureImg.onload = () => { signLoaded = true; tryFinish(); };
            signatureImg.onerror = () => { signLoaded = true; tryFinish(); };
        }

        function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
            const words = text.split(" ");
            let line = "";

            for (let n = 0; n < words.length; n++) {
                const testLine = line + words[n] + " ";
                if (ctx.measureText(testLine).width > maxWidth && n > 0) {
                    ctx.fillText(line, x, y);
                    line = words[n] + " ";
                    y += lineHeight;
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line, x, y);
        }

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

        // ✅ LOAD START
        let started = false;

        function startOnce() {
            if (started) return;
            started = true;
            drawAll();
        }

        logo.onload = startOnce;
        logo.onerror = startOnce;

        // fallback (only if logo fails to load)
        setTimeout(startOnce, 500);
    }

});