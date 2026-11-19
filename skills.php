<?php $title = 'My Skills'; include 'includes/header.php';
$skills = db()->query('SELECT icon, name, description FROM skills ORDER BY sort_order, id')->fetchAll(); ?>
	<section class="page-header">
		<p>WHAT CAN I DO?</p>
		<h1>My Skills</h1>
		<p>Some of the technical and creative skills I have developed.</p>
	</section>
	<section class="skills-container">
		<?php foreach ($skills as $s): ?>
		<div class="skill-card">
			<div class="skill-icon"><?= e($s['icon']) ?></div>
			<h2><?= e($s['name']) ?></h2>
			<p><?= e($s['description']) ?></p>
		</div>
		<?php endforeach; ?>
	</section>
<?php include 'includes/footer.php'; ?>
