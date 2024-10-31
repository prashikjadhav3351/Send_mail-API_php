<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nts";

// Email settings
$toEmail = "prashikjadhav90031@gmail.com"; // Your email address

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data with validation
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Handle file upload
    $resume = $_FILES["resume"];
    $resumePath = "uploads/" . basename($resume["name"]);

    // Check if file was uploaded without errors
    if ($resume["error"] === UPLOAD_ERR_OK) {
        move_uploaded_file($resume["tmp_name"], $resumePath);
    } else {
        die("Error uploading file: " . $resume["error"]);
    }

    // Insert data into MySQL database
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind SQL statement
    $sql = "INSERT INTO form_submissions (name, email, phone, resume) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $phone, $resumePath);

    if ($stmt->execute()) {
        echo "Form submitted successfully!";

        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // SMTP settings (Replace with your SMTP provider settings)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Use Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'prashikjadhav90031@gmail.com';  // Your Gmail address
            $mail->Password = 'vcysqmlsvaczjotd';   // Your App Password
            $mail->SMTPSecure = 'tls'; // Use 'ssl' if your SMTP provider requires it
            $mail->Port = 587; // Usually 587 for TLS or 465 for SSL

            // Email settings
            $mail->setFrom($toEmail, 'Form Submission'); // Set the sender
            $mail->addAddress($toEmail); // Add your email as the recipient
            $mail->isHTML(true);
            $mail->Subject = "New Form Submission";
            $mail->Body = "
                <h3>New form submission details:</h3>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Resume:</strong> <a href='http://localhost/DB_TEST/$resumePath'>Download Resume</a></p>
            ";

            // Send the email
            $mail->send();
            echo "Email has been sent successfully!";
        } catch (Exception $e) {
            echo "Error in sending email: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} 
?>
