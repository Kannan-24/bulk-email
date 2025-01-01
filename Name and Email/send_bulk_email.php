<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

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
    $templatePath = 'C:/xampp/htdocs/bulk-email/Mail_template.html';

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

        while (($data = fgetcsv($handle)) !== FALSE) {
            $name = $data[0]; 
            $email = $data[1]; 
            $company = $data[2]; 

            $personalizedEmail = str_replace(['{name}', '{company}'], [$name, $company], $emailTemplate);
            sendEmail($name, $email, $personalizedEmail);
        }

        fclose($handle);
    } else {
        echo "Error opening the CSV file.";
    }
}

function sendEmail($name, $email, $personalizedEmailContent) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.mail.yahoo.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skmandcompany@yahoo.in';
        $mail->Password = 'bhkgthojqmzwfaff';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('skmandcompany@yahoo.in', 'SKM AND COMPANY');
        $mail->addAddress($email, $name);

        // Add inline image (image.jpg located in the assets directory)
        $mail->addEmbeddedImage('assets/New_Year.png', 'inlineImage'); // Add your image path here

        // Replace the placeholder in the email template with the inline image
        $personalizedEmailContent = str_replace(
            '{image}',
            '<img src="cid:inlineImage" alt="New Year Image" style="max-width: 50%;">',
            $personalizedEmailContent
        );

        $mail->isHTML(true);
        $mail->Subject = 'Wishing You a Prosperous New Year from SKM AND COMPANY!';
        $mail->Body    = $personalizedEmailContent;

        $mail->send();
        echo "Message sent to $email<br>";
    } catch (Exception $e) {
        echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}<br>";
    }
}
?>
