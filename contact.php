<?php
$title = 'Contact Me';
require_once 'config.php';
$errors = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // Honeypot: real visitors never see this field, bots fill it in. Pretend success and save nothing.
    if (!empty($_POST['website'])) {
        $_SESSION['flash'] = 'Thank you! Your message has been sent.';
        header('Location: contact.php');
        exit;
    }
    $old['name']    = trim((string)($_POST['name'] ?? ''));
    $old['email']   = trim((string)($_POST['email'] ?? ''));
    $old['subject'] = trim((string)($_POST['subject'] ?? ''));
    $old['message'] = trim((string)($_POST['message'] ?? ''));

    if ($old['name'] === '' || mb_strlen($old['name']) > 100) $errors['name'] = 'Please enter your name (max 100 characters).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) $errors['email'] = 'Please enter a valid email.';
    if ($old['subject'] === '' || mb_strlen($old['subject']) > 150) $errors['subject'] = 'Please enter a subject (max 150 characters).';
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) $errors['message'] = 'Message must be 10-2000 characters.';

    // simple rate limit: 1 message per 30 seconds per session
    if (!array_filter($errors) && time() - ($_SESSION['last_msg'] ?? 0) < 30) {
        $errors['message'] = 'Please wait a moment before sending another message.';
    }
    if (!array_filter($errors)) {
        $st = db()->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $st->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
        $_SESSION['last_msg'] = time();
        $_SESSION['flash'] = 'Thank you! Your message has been sent.';
        header('Location: contact.php');
        exit;
    }
}
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
include 'includes/header.php';
?>
	<section class="page-header">
		<p>LET'S CONNECT!</p>
		<h1>Contact Me</h1>
		<p>Have a question or want to get in touch? Send me a message!</p>
	</section>
	<section class="contact-section">
		<div class="contact-info">
			<h2>Get in Touch</h2>
			<p>Feel free to contact me through email or social media.</p>
			<?php $pr = profile(); ?>
			<?php if ($pr['email'] !== ''): ?><div class="contact-detail">📧 <span><?= e($pr['email']) ?></span></div><?php endif; ?>
			<?php if ($pr['facebook'] !== ''): ?><div class="contact-detail">📱 <span>Facebook: <?= e($pr['facebook']) ?></span></div><?php endif; ?>
			<?php if ($pr['instagram'] !== ''): ?><div class="contact-detail">📱 <span>Instagram: <?= e($pr['instagram']) ?></span></div><?php endif; ?>
		</div>
		<form id="contactForm" class="contact-form" method="post" action="contact.php" novalidate>
			<?= csrf_field() ?>
			<label for="name">Name</label>
			<input type="text" id="name" name="name" placeholder="Enter your name" maxlength="100" value="<?= e($old['name']) ?>">
			<small id="nameError"><?= e($errors['name']) ?></small>

			<label for="email">Email</label>
			<input type="email" id="email" name="email" placeholder="Enter your email" maxlength="150" value="<?= e($old['email']) ?>">
			<small id="emailError"><?= e($errors['email']) ?></small>

			<label for="subject">Subject</label>
			<input type="text" id="subject" name="subject" placeholder="Enter a subject" maxlength="150" value="<?= e($old['subject']) ?>">
			<small id="subjectError"><?= e($errors['subject']) ?></small>

			<label for="message">Message</label>
			<textarea id="message" name="message" rows="6" placeholder="Write your message" maxlength="2000"><?= e($old['message']) ?></textarea>
			<small id="messageError"><?= e($errors['message']) ?></small>

			<div class="hp" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<button type="submit" class="btn">Send Message</button>
			<p id="formMessage"><?= e($flash) ?></p>
		</form>
	</section>
<?php include 'includes/footer.php'; ?>
