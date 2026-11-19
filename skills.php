<?php $title = 'My Skills'; include 'includes/header.php';
$rows = db()->query("SELECT icon, name, description, category FROM skills ORDER BY FIELD(category, 'Technical', 'Creative', 'Soft skills', 'Other'), sort_order, id")->fetchAll();
$groups = [];
foreach ($rows as $r) { $groups[$r['category']][] = $r; }
?>
	<section class="page-header">
		<p>WHAT CAN I DO?</p>
		<h1>My Skills</h1>
		<p>Some of the technical and creative skills I have developed.</p>
	</section>
	<?php foreach ($groups as $category => $items): ?>
	<h2 class="skill-group"><?= e($category) ?></h2>
	<section class="skills-container">
		<?php foreach ($items as $s): ?>
		<div class="skill-card">
			<div class="skill-icon"><?= e($s['icon']) ?></div>
			<h2><?= e($s['name']) ?></h2>
			<p><?= e($s['description']) ?></p>
		</div>
		<?php endforeach; ?>
	</section>
	<?php endforeach; ?>
<?php include 'includes/footer.php'; ?>
