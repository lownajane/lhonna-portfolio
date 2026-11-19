<?php $title = 'About Me'; include 'includes/header.php';
$cards = db()->query('SELECT heading, body FROM about_sections ORDER BY sort_order, id')->fetchAll(); ?>
	<section class="page-header">
		<p>WHO AM I?</p>
		<h1>About Me</h1>
		<p>A little introduction about who I am, my education, goals, and interests.</p>
	</section>
	<section class="content-section">
		<?php foreach ($cards as $c): ?>
		<div class="about-card">
			<h2><?= e($c['heading']) ?></h2>
			<p><?= e($c['body']) ?></p>
		</div>
		<?php endforeach; ?>
	</section>
<?php include 'includes/footer.php'; ?>
