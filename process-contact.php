<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$type = trim($_POST['form_type'] ?? 'Contact');
$senderName = trim($_POST['full_name'] ?? '');
$senderPhone = trim($_POST['phone'] ?? '');
$senderEmail = trim($_POST['email'] ?? '');
$service = trim($_POST['subject'] ?? $_POST['service'] ?? 'Général');
$subject = trim($_POST['subject'] ?? 'Demande de renseignement BTP');
$message = trim($_POST['message'] ?? '');

if (empty($senderName) || empty($senderPhone)) {
    $_SESSION['flash_error'] = "Veuillez renseigner au minimum votre nom et votre numéro de téléphone.";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'contact.php'));
    exit;
}

$attachmentPath = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['attachment']['tmp_name'];
    $fileName = $_FILES['attachment']['name'];
    $fileSize = $_FILES['attachment']['size'];
    
    // Extensions autorisées (plans, PDF, images)
    $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'dwg'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowedExts) && $fileSize <= 15 * 1024 * 1024) { // max 15 Mo
        $newFileName = 'piece_jointe_' . time() . '_' . uniqid() . '.' . $ext;
        $destDir = __DIR__ . '/uploads/attachments/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
        $destPath = $destDir . $newFileName;
        if (move_uploaded_file($fileTmp, $destPath)) {
            $attachmentPath = 'uploads/attachments/' . $newFileName;
        }
    }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (type, sender_name, sender_email, sender_phone, service_requested, subject, message, attachment, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'unread')
    ");
    $stmt->execute([
        $type,
        $senderName,
        $senderEmail,
        $senderPhone,
        $service,
        $subject,
        $message,
        $attachmentPath
    ]);

    $_SESSION['flash_success'] = "Votre demande a bien été transmise à la direction technique de FEE BTP SARL. Nous vous recontacterons dans les plus brefs délais !";
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Une erreur est survenue lors de l'enregistrement de votre message : " . $e->getMessage();
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'contact.php';
header("Location: " . $redirect);
exit;
