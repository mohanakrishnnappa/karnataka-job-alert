<?php
if (!defined('ABSPATH')) exit;

function wzq_certificate_ui() {
?>

    <!-- 🧾 POPUP -->
    <div id="wzq-cert-popup" class="wzq-modal">
        <div class="wzq-modal-box">
            <p>Enter Your Name</p>
            <input type="text" id="wzq-cert-name" placeholder="Full Name" style="width:100%;padding:10px;">
            
            <div class="wzq-modal-actions">
                <button id="wzq-cert-save">Download</button>
                <button id="wzq-cert-cancel">Cancel</button>
            </div>
        </div>
    </div>
<?php
}