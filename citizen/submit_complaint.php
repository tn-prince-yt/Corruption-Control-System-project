<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireCitizen();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - Citizen</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/navbar.php'; ?>

        <div class="dashboard">
            <div class="sidebar">
                <div class="user-profile">
                    <h3><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h3>
                    <p><?php echo $_SESSION['email']; ?></p>
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="submit_complaint.php" class="active">Submit Complaint</a>
                    <a href="view_complaints.php">My Complaints</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <h2>Submit a Complaint</h2>
                
                <?php
                if (isset($_SESSION['form_error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['form_error'] . '</div>';
                    unset($_SESSION['form_error']);
                }
                if (isset($_SESSION['form_success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['form_success'] . '</div>';
                    unset($_SESSION['form_success']);
                }
                ?>

                <form method="POST" action="process_complaint.php" enctype="multipart/form-data" class="form">
                    <div class="form-group">
                        <label for="title">Complaint Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="bribery">Bribery</option>
                            <option value="embezzlement">Embezzlement</option>
                            <option value="fraud">Fraud</option>
                            <option value="misconduct">Misconduct</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required>
                    </div>

                    <div class="form-group">
                        <label for="complaint_date">Date of Incident</label>
                        <input type="date" id="complaint_date" name="complaint_date" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Complaint Description</label>
                        <textarea id="description" name="description" rows="6" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="evidence">Upload Evidence (Images, Documents)</label>
                        <input type="file" id="evidence" name="evidence[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                        <small>Max file size: 5MB. Allowed formats: jpg, jpeg, png, gif, pdf, doc, docx</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
