<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= e($title ?? "Lhonna's Personal Portfolio") ?></title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<header>
		<nav class="navbar">
			<a href="index.php" class="logo">Lhonna</a>
			<button class="menu-btn" id="menuBtn" aria-label="Open Navigation">☰</button>
			<ul class="nav-links" id="navLinks">
				<li><a href="index.php">Home</a></li>
				<li><a href="about.php">About</a></li>
				<li><a href="skills.php">Skills</a></li>
				<li><a href="projects.php">Projects</a></li>
				<li><a href="resume.php">Resume</a></li>
				<li><a href="contact.php">Contact</a></li>
			</ul>
			<button id="themeBtn" class="theme-btn">🌙</button>
		</nav>
	</header>
	<main>
