<?php $title = 'Resume & Experience'; include 'includes/header.php';
$items = db()->query('SELECT label, title, description FROM resume_items ORDER BY sort_order, id')->fetchAll(); ?>
	<section class="page-header">
		<p>MY BACKGROUND</p>
		<h1>Resume &amp; Experience</h1>
		<p>My education, activities, training, and experiences.</p>
	</section>
	<section class="resume-section">
		<?php foreach ($items as $r): ?>
		<div class="resume-item">
			<div class="resume-date"><?= e($r['label']) ?></div>
			<div class="resume-content">
				<h2><?= e($r['title']) ?></h2>
				<p><?= e($r['description']) ?></p>
			</div>
		</div>
		<?php endforeach; ?>
	</section>
<?php include 'includes/footer.php'; ?>
