<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Submit'])) {
    // Sanitize inputs
    $customerName = htmlspecialchars(trim($_POST['name']));
    $customerEmail = htmlspecialchars(trim($_POST['email']));
      $country_code = htmlspecialchars(trim($_POST['country_code']));
    
    $phoneNumber = htmlspecialchars(trim($_POST['mobilenumber']));

    // Validate phone number
    if (!preg_match('/^\d{10}$/', $phoneNumber)) {
        die("<script>alert('Invalid phone number. Must be exactly 10 digits.'); window.history.back();</script>");
    }

    // Validate email format
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        die("<script>alert('Invalid email format.'); window.history.back();</script>");
    }

    // Blocked domains and keywords
    $blockedDomains = ['yahoo.com','automisly.org', 'carrotquest-mail.io', 'scamdomain.com', 'exampleblocked.org'];
    $blockedKeywords = ['BTC', 'bitcoin', 'crypto', 'carrotquest', 'phishing', 'investment', 'forex', 'profit', 'telegram'];

    $emailDomain = substr(strrchr($customerEmail, "@"), 1);

    function logSuspiciousAttempt($name, $email, $phone) {
        file_put_contents('suspicious_leads.log', date('Y-m-d H:i:s') . " - Suspicious Attempt: Name: $name, Email: $email, Phone: $phone" . PHP_EOL, FILE_APPEND);
    }

    // Check for blocked domain
    if (in_array($emailDomain, $blockedDomains)) {
        logSuspiciousAttempt($customerName, $customerEmail, $phoneNumber);
        die("<script>alert('Submission blocked: Suspicious email domain.'); window.history.back();</script>");
    }

    // Check for blocked keywords
    $inputData = strtolower($customerName . " " . $customerEmail . " " . $phoneNumber);
    foreach ($blockedKeywords as $keyword) {
        if (strpos($inputData, strtolower($keyword)) !== false) {
            logSuspiciousAttempt($customerName, $customerEmail, $phoneNumber);
            die("<script>alert('Submission blocked: Suspicious content detected.'); window.history.back();</script>");
        }
    }
    
    if (preg_match('/telegra\.ph/', $inputData)) {
        logSuspiciousAttempt($customerName, $customerEmail, $phoneNumber);
        die("<script>alert('Submission blocked: Suspicious URL detected.'); window.history.back();</script>");
    }

    // Database connection
    $conn = new mysqli('localhost', 'srikrishnaDeveloper', 'leads@123', 'srikrishnaDeveloper');

    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        die("<script>alert('Database connection failed. Please try again later.');</script>");
    }

    // Check for duplicates
    $sql_check = "SELECT * FROM krishe_avya WHERE Email=? OR Mobile=?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ss', $customerEmail, $phoneNumber);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email or phone number already exists.'); window.history.back();</script>";
        $stmt_check->close();
        $conn->close();
        exit;
    }

    // Insert new data
    $currentDateTime = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO krishe_avya (Name, Email,country_code, Mobile, Date) VALUES (?, ?, ?, ?,?)");
    $stmt->bind_param('sssss', $customerName, $customerEmail,$country_code, $phoneNumber, $currentDateTime);

    if ($stmt->execute()) {
        echo "<script>window.location.href = 'index.php';</script>";
    } else {
        error_log("Database insertion failed: " . $stmt->error);
        die("<script>alert('Error saving data. Please try again later.');</script>");
    }

    $stmt_check->close();
    $stmt->close();
    $conn->close();

    // API call
    $crm_api_url = "https://srikrishnadeveloppers.tranquilcrmone.in/mobileapp/mblead?api_key=&mobile_number=" . urlencode($phoneNumber) . "&customer_name=" . urlencode($customerName) . "&email=" . urlencode($customerEmail) . "&country_code=" . urlencode($country_code) . "&lead_project_nm=" . urlencode("2") . "&source_type=" . urlencode("3");

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $crm_api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => ['Cookie: ci_session=kt7tve86frcqoi04v1b9udvop1nr3b5c'],
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        error_log("CRM API failed: $curlError");
        die("<script>alert('Error in CRM integration.');</script>");
    }
}
?>
