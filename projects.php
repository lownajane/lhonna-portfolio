<?php $title = 'My Projects'; include 'includes/header.php';
$projects = db()->query('SELECT * FROM projects ORDER BY sort_order, id')->fetchAll(); ?>
	<section class="page-header">
		<p>WHAT HAVE I CREATED?</p>
		<h1>Projects</h1>
		<p>Here are some of the projects I worked on in school and on my own.</p>
	</section>
	<section class="project-section">
		<div class="filter-buttons">
			<button class="filter-btn active" data-filter="all">All</button>
			<button class="filter-btn" data-filter="web">Web</button>
			<button class="filter-btn" data-filter="networking">Networking</button>
			<button class="filter-btn" data-filter="animation">Animation</button>
		</div>
		<div class="project-grid">
			<?php foreach ($projects as $p): ?>
			<article class="project-card" data-category="<?= e($p['category']) ?>">
				<img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>">
				<div class="project-content">
					<span><?= e(ucfirst($p['category'])) ?></span>
					<h2><?= e($p['title']) ?></h2>
					<p><?= e($p['description']) ?></p>
					<p class="technology"><strong>Technology:</strong> <?= e($p['technology']) ?></p>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
	</section>
<?php include 'includes/footer.php'; ?>
