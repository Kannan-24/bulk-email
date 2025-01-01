<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Handle the uploaded file
    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
    $fileName = $_FILES['csv_file']['name'];

    // Check if the uploaded file is a CSV
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    if ($fileExtension !== 'csv') {
        die("Please upload a valid CSV file.");
    }

    // Specify the path to the HTML template
    $templatePath = 'C:/xampp/htdocs/bulk-email/Mail.html';

    // Process the uploaded CSV file
    sendBulkEmails($fileTmpPath, $templatePath);
} else {
    echo "No file uploaded.";
}

function sendBulkEmails($csvFilePath, $templatePath) {
    if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
        fgetcsv($handle); // Skip the header row

        // Load the email template from the specified path
        $emailTemplate = file_get_contents($templatePath);
        if ($emailTemplate === false) {
            die("Failed to load email template.");
        }

        // Loop through each row of the CSV file
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Debugging: Check the CSV data being processed
            var_dump($data); // Show the parsed CSV row content

            // Check if the email is valid
            if (isset($data[0]) && filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                $email = $data[0]; // Assuming email is in the first column
                sendEmail($email, $emailTemplate); // Send the email
            } else {
                echo "Invalid or missing email: {$data[0]}<br>";
            }
        }

        fclose($handle);
    } else {
        echo "Error opening the CSV file.";
    }
}

function sendEmail($email, $personalizedEmailContent) {
    $mail = new PHPMailer(true);

    try {
        // Set up the SMTP server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.mail.yahoo.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skmandcompany@yahoo.in';
        $mail->Password = 'bhkgthojqmzwfaff'; // Replace with your actual password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set up the email headers
        $mail->setFrom('skmandcompany@yahoo.in', 'SKM AND COMPANY');
        $mail->addAddress($email);

        // Add inline image (New_Year.png located in the assets directory)
        $mail->addEmbeddedImage('assets/New_Year.png', 'inlineImage'); // Adjust path as needed

        // Replace the placeholder in the email template with the inline image
        $personalizedEmailContent = str_replace(
            '{image}',
            '<img src="cid:inlineImage" alt="New Year Image" style="max-width: 50%;">',
            $personalizedEmailContent
        );

        // Set email format to HTML
        $mail->isHTML(true);
        $mail->Subject = 'Wishing You a Prosperous New Year from SKM AND COMPANY!';
        $mail->Body    = $personalizedEmailContent;

        // Send the email
        $mail->send();
        echo "Message sent to $email<br>";
    } catch (Exception $e) {
        echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}<br>";
    }
}
?>
