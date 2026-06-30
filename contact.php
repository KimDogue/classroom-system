<?php
session_start();

if(!isset($_SESSION['name'])){
    header("Location: index.php");
    exit();
}

$submitted = false;
if(isset($_POST['send'])){
    // In a real system, you could save this to a DB or send via email.
    // For now, we just show a success message.
    $submitted = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact & Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #fff5f7;
        color: #3b1a2a;
        min-height: 100vh;
    }

    /* TOPBAR */
    .topbar {
        background: #db2777;
        color: white;
        padding: 18px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(219,39,119,0.25);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .topbar h2 { font-size: 20px; font-weight: 800; }
    .topbar-links { display: flex; gap: 12px; }
    .topbar a {
        text-decoration: none;
        color: #db2777;
        background: white;
        padding: 9px 22px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.2s;
    }
    .topbar a:hover { background: #fce7f3; transform: translateY(-1px); }
    .topbar a.outline {
        background: transparent;
        color: white;
        border: 1px solid rgba(255,255,255,0.5);
    }
    .topbar a.outline:hover { background: rgba(255,255,255,0.15); }

    /* HERO */
    .hero {
        background: linear-gradient(135deg, #db2777 0%, #be185d 60%, #9d174d 100%);
        color: white;
        padding: 50px 40px;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 280px; height: 280px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: 30%;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .hero h1 { font-size: 30px; font-weight: 800; margin-bottom: 8px; position: relative; z-index: 1; }
    .hero p { font-size: 15px; opacity: 0.85; position: relative; z-index: 1; max-width: 500px; line-height: 1.6; }
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        padding: 5px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 16px;
        position: relative; z-index: 1;
        backdrop-filter: blur(4px);
    }

    /* CONTAINER */
    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }

    /* CARD */
    .card {
        background: white;
        border-radius: 20px;
        border: 1px solid #fce7f3;
        box-shadow: 0 2px 12px rgba(219,39,119,0.05);
        padding: 28px;
    }
    .card-title {
        font-size: 16px;
        font-weight: 800;
        color: #be185d;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* CONTACT INFO ITEMS */
    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px;
        background: #fff0f5;
        border-radius: 12px;
        border: 1px solid #fce7f3;
        margin-bottom: 14px;
        transition: all 0.2s;
    }
    .contact-item:hover { border-color: #f9a8d4; transform: translateX(4px); }
    .contact-item:last-child { margin-bottom: 0; }
    .contact-icon {
        width: 44px; height: 44px;
        background: linear-gradient(135deg, #fce7f3, #fbcfe8);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .contact-label { font-size: 11px; font-weight: 700; color: #c084a0; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
    .contact-value { font-size: 14px; font-weight: 600; color: #be185d; }
    .contact-sub   { font-size: 12px; color: #9d6478; margin-top: 2px; }

    /* FAQ */
    .faq-item {
        border: 1px solid #fce7f3;
        border-radius: 12px;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .faq-item:last-child { margin-bottom: 0; }
    .faq-question {
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #be185d;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff8fa;
        transition: background 0.2s;
        user-select: none;
    }
    .faq-question:hover { background: #fff0f5; }
    .faq-question .arrow { transition: transform 0.3s; font-size: 12px; }
    .faq-answer {
        padding: 0 16px;
        font-size: 13px;
        color: #9d6478;
        line-height: 1.7;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }
    .faq-item.open .faq-answer { max-height: 200px; padding: 12px 16px; }
    .faq-item.open .arrow { transform: rotate(180deg); }

    /* FORM */
    .form-group { margin-bottom: 16px; }
    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #c084a0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 6px;
    }
    .form-input, .form-textarea, .form-select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #fce7f3;
        border-radius: 12px;
        font-size: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #3b1a2a;
        background: #fff8fa;
        transition: all 0.2s;
    }
    .form-input:focus, .form-textarea:focus, .form-select:focus {
        outline: none;
        border-color: #db2777;
        background: white;
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }
    .form-textarea { resize: vertical; min-height: 110px; }
    .form-select { appearance: none; cursor: pointer; }

    .send-btn {
        width: 100%;
        padding: 13px;
        background: linear-gradient(135deg, #db2777, #be185d);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Plus Jakarta Sans', sans-serif;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(219,39,119,0.3);
    }
    .send-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(219,39,119,0.4); }

    /* SUCCESS */
    .success-box {
        text-align: center;
        padding: 40px 20px;
    }
    .success-box .success-icon { font-size: 50px; margin-bottom: 16px; }
    .success-box h3 { font-size: 20px; font-weight: 800; color: #16a34a; margin-bottom: 8px; }
    .success-box p { font-size: 14px; color: #9d6478; margin-bottom: 20px; line-height: 1.6; }
    .back-btn {
        display: inline-block;
        padding: 12px 28px;
        background: linear-gradient(135deg, #db2777, #be185d);
        color: white;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(219,39,119,0.3);
    }
    .back-btn:hover { transform: translateY(-1px); }

    @media(max-width: 700px){
        .container { grid-template-columns: 1fr; }
        .topbar { padding: 15px 20px; flex-direction: column; gap: 12px; text-align: center; }
        .hero { padding: 28px 20px; }
        .hero h1 { font-size: 24px; }
    }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <h2>💬 Contact & Support</h2>
    <div class="topbar-links">
        <a href="dashboard.php" class="outline">← Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <div class="hero-badge">🛎️ Support Center</div>
    <h1>How can we help you?</h1>
    <p>Have a concern or need assistance with your classroom reservation? Send us a message and our admin team will get back to you as soon as possible.</p>
</div>

<div class="container">

    <!-- LEFT COLUMN -->
    <div style="display:flex; flex-direction:column; gap:24px;">

        <!-- CONTACT INFO -->
        <div class="card">
            <div class="card-title">📞 Contact Information</div>

            <div class="contact-item">
                <div class="contact-icon">🏫</div>
                <div>
                    <div class="contact-label">Office</div>
                    <div class="contact-value">Registrar's Office</div>
                    <div class="contact-sub">Main Building, Ground Floor</div>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">📧</div>
                <div>
                    <div class="contact-label">Email</div>
                    <div class="contact-value">admin@classroom.edu.ph</div>
                    <div class="contact-sub">Response within 1–2 business days</div>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">📞</div>
                <div>
                    <div class="contact-label">Phone</div>
                    <div class="contact-value">(02) 8123-4567</div>
                    <div class="contact-sub">Mon – Fri, 8:00 AM – 5:00 PM</div>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">🕐</div>
                <div>
                    <div class="contact-label">Office Hours</div>
                    <div class="contact-value">Mon – Fri</div>
                    <div class="contact-sub">8:00 AM – 5:00 PM</div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="card">
            <div class="card-title">❓ Frequently Asked Questions</div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How do I reserve a classroom?
                    <span class="arrow">▼</span>
                </div>
                <div class="faq-answer">
                    Go to your Dashboard, select a floor, then choose an Available room. Fill in your preferred date, start time, and end time, then click "Reserve This Room."
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Can I cancel my reservation?
                    <span class="arrow">▼</span>
                </div>
                <div class="faq-answer">
                    Yes. Go to the floor where your reserved room is located and click the "Cancel My Reservation" button on your room card.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    What if a room shows Occupied but I need it?
                    <span class="arrow">▼</span>
                </div>
                <div class="faq-answer">
                    Please contact the admin office directly. An admin can cancel an existing reservation if there is a valid reason.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How do I change my password?
                    <span class="arrow">▼</span>
                </div>
                <div class="faq-answer">
                    Go to your Profile page. Scroll to the "Edit Profile" section and enter your new password in the "New Password" and "Confirm Password" fields, then save.
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: CONTACT FORM -->
    <div class="card">
        <div class="card-title">✉️ Send a Message</div>

        <?php if($submitted): ?>
        <div class="success-box">
            <div class="success-icon">✅</div>
            <h3>Message Sent!</h3>
            <p>Thank you for reaching out. Our admin team will review your concern and get back to you within 1–2 business days.</p>
            <a href="contact.php" class="back-btn">Send Another Message</a>
        </div>
        <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Your Name</label>
                <input type="text" name="sender_name" class="form-input"
                       value="<?php echo $_SESSION['name']; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Concern Type</label>
                <select name="concern_type" class="form-select" required>
                    <option value="" disabled selected>Select a concern...</option>
                    <option value="reservation">Room Reservation Issue</option>
                    <option value="cancellation">Cancellation Request</option>
                    <option value="account">Account / Login Problem</option>
                    <option value="room">Room Condition / Facilities</option>
                    <option value="schedule">Schedule Conflict</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-input"
                       placeholder="Brief description of your concern" required>
            </div>

            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-textarea"
                          placeholder="Describe your concern in detail..." required></textarea>
            </div>

            <button type="submit" name="send" class="send-btn">📨 Send Message</button>
        </form>
        <?php endif; ?>
    </div>

</div>

<script>
function toggleFaq(el){
    const item = el.parentElement;
    item.classList.toggle('open');
}
</script>

</body>
</html>