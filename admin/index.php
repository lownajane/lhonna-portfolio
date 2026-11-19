<?php
require_once __DIR__ . '/../config.php';
require_admin();
if (time() - ($_SESSION['last'] ?? time()) > 1800) { $_SESSION = []; session_destroy(); header('Location: login.php?expired=1'); exit; }
$_SESSION['last'] = time();

$cats = ['web', 'networking', 'animation'];
$tabs = ['overview' => 'Overview', 'messages' => 'Messages', 'projects' => 'Projects', 'skills' => 'Skills', 'resume' => 'Resume', 'account' => 'Account'];
$tables = ['project' => 'projects', 'skill' => 'skills', 'resume' => 'resume_items', 'message' => 'messages'];

function flash(string $m, bool $ok = true): void { $_SESSION['flash'] = [$ok, $m]; }
function v(?array $r, string $k): string { return e($r[$k] ?? ''); }
function drop_image(string $p): void { if (preg_match('#^uploads/[a-f0-9]{24}\.(jpg|png|webp|gif)$#', $p)) @unlink(__DIR__ . '/../' . $p); }
function save_image(array $f): ?string {
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024) { flash('Image upload failed (max 2 MB).', false); return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset($types[$mime]) || @getimagesize($f['tmp_name']) === false) { flash('Only JPG, PNG, WEBP or GIF images are allowed.', false); return null; }
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) mkdir($dir, 0755);
    $name = bin2hex(random_bytes(12)) . '.' . $types[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) { flash('Could not save the image.', false); return null; }
    return 'uploads/' . $name;
}
function del_form(string $what, int $id, string $tab): string {
    return '<form method="post" onsubmit="return confirm(\'Delete this item?\')">' . csrf_field() .
        '<input type="hidden" name="action" value="delete"><input type="hidden" name="what" value="' . e($what) . '">' .
        '<input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="tab" value="' . e($tab) . '">' .
        '<button class="danger" type="submit">Delete</button></form>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $tab = array_key_exists($_POST['tab'] ?? '', $tabs) ? $_POST['tab'] : 'overview';
    $t = fn(string $k, int $n): string => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $n);
    $pdo = db();

    if ($a === 'save_project') {
        $cat = in_array($_POST['category'] ?? '', $cats, true) ? $_POST['category'] : 'web';
        $file = $_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
        $img = null;
        if ($file['error'] !== UPLOAD_ERR_NO_FILE && ($img = save_image($file)) === null) { header('Location: index.php?tab=projects'); exit; }
        if ($id) {
            if ($img) { $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?'); $st->execute([$id]); drop_image((string)$st->fetchColumn()); }
            $pdo->prepare('UPDATE projects SET title=?, category=?, description=?, technology=?, image=COALESCE(?, image) WHERE id=?')
                ->execute([$t('title', 150), $cat, $t('description', 1000), $t('technology', 150), $img, $id]);
            flash('Project updated.');
        } elseif ($img) {
            $pdo->prepare('INSERT INTO projects (title, category, description, technology, image) VALUES (?,?,?,?,?)')
                ->execute([$t('title', 150), $cat, $t('description', 1000), $t('technology', 150), $img]);
            flash('Project added.');
        } else { flash('Please choose an image.', false); }
    } elseif ($a === 'save_skill') {
        $vals = [$t('icon', 10), $t('name', 100), $t('description', 255)];
        if ($id) { $pdo->prepare('UPDATE skills SET icon=?, name=?, description=? WHERE id=?')->execute([...$vals, $id]); flash('Skill updated.'); }
        else { $pdo->prepare('INSERT INTO skills (icon, name, description) VALUES (?,?,?)')->execute($vals); flash('Skill added.'); }
    } elseif ($a === 'save_resume') {
        $vals = [$t('label', 50), $t('title', 150), $t('description', 1000)];
        if ($id) { $pdo->prepare('UPDATE resume_items SET label=?, title=?, description=? WHERE id=?')->execute([...$vals, $id]); flash('Resume item updated.'); }
        else { $pdo->prepare('INSERT INTO resume_items (label, title, description) VALUES (?,?,?)')->execute($vals); flash('Resume item added.'); }
    } elseif ($a === 'toggle_read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 - is_read WHERE id = ?')->execute([$id]);
    } elseif ($a === 'delete' && isset($tables[$_POST['what'] ?? ''])) {
        $tbl = $tables[$_POST['what']];
        if ($tbl === 'projects') { $st = $pdo->prepare('SELECT image FROM projects WHERE id = ?'); $st->execute([$id]); drop_image((string)$st->fetchColumn()); }
        $pdo->prepare("DELETE FROM $tbl WHERE id = ?")->execute([$id]);
        flash('Deleted.');
    } elseif ($a === 'change_password') {
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $st->execute([$_SESSION['admin_id']]);
        $new = (string)($_POST['new'] ?? '');
        if (!password_verify((string)($_POST['current'] ?? ''), (string)$st->fetchColumn())) flash('Current password is incorrect.', false);
        elseif (strlen($new) < 12) flash('New password must be at least 12 characters.', false);
        elseif ($new !== ($_POST['confirm'] ?? '')) flash('New passwords do not match.', false);
        else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            session_regenerate_id(true);
            flash('Password changed.');
        }
    }
    header('Location: index.php?tab=' . $tab); exit;
}

$tab = array_key_exists($_GET['tab'] ?? '', $tabs) ? $_GET['tab'] : 'overview';
$edit = null;
$editMap = ['projects' => 'projects', 'skills' => 'skills', 'resume' => 'resume_items'];
if (isset($_GET['edit'], $editMap[$tab])) {
    $st = db()->prepare('SELECT * FROM ' . $editMap[$tab] . ' WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$projects = db()->query('SELECT * FROM projects ORDER BY id')->fetchAll();
$skills   = db()->query('SELECT * FROM skills ORDER BY id')->fetchAll();
$resume   = db()->query('SELECT * FROM resume_items ORDER BY id')->fetchAll();
$messages = db()->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
$unread   = count(array_filter($messages, fn($m) => empty($m['is_read'])));
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$self = 'index.php?tab=' . $tab;
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin · <?= e($tabs[$tab]) ?></title><link rel="stylesheet" href="../style.css"><link rel="stylesheet" href="admin.css"></head>
<body class="admin">
<aside class="sidebar">
  <a href="../index.php" class="logo">Lhonna</a><small>Admin panel</small>
  <nav>
  <?php foreach ($tabs as $k => $label): ?>
    <a href="index.php?tab=<?= e($k) ?>" class="<?= $k === $tab ? 'active' : '' ?>"><?= e($label) ?><?php if ($k === 'messages' && $unread): ?><span class="badge"><?= $unread ?></span><?php endif; ?></a>
  <?php endforeach; ?>
  </nav>
  <a href="../index.php" class="side-link">← View site</a>
  <a href="logout.php" class="side-link">Log out</a>
</aside>
<main class="admin-main">
<h1><?= e($tabs[$tab]) ?></h1>
<?php if ($flash): ?><p class="flash <?= $flash[0] ? 'ok' : 'bad' ?>"><?= e($flash[1]) ?></p><?php endif; ?>

<?php if ($tab === 'overview'): ?>
<div class="stats">
  <div class="stat"><b><?= count($messages) ?></b><span>Messages (<?= $unread ?> new)</span></div>
  <div class="stat"><b><?= count($projects) ?></b><span>Projects</span></div>
  <div class="stat"><b><?= count($skills) ?></b><span>Skills</span></div>
  <div class="stat"><b><?= count($resume) ?></b><span>Resume items</span></div>
</div>
<h2>Latest messages</h2>
<?php foreach (array_slice($messages, 0, 3) as $m): ?>
<div class="card msg <?= empty($m['is_read']) ? 'unread' : '' ?>"><b><?= e($m['name']) ?></b> <small><?= e($m['email']) ?> · <?= e($m['created_at']) ?></small><p><?= nl2br(e($m['message'])) ?></p></div>
<?php endforeach; if (!$messages): ?><p class="muted">No messages yet.</p><?php endif; ?>

<?php elseif ($tab === 'messages'): ?>
<?php foreach ($messages as $m): ?>
<div class="card msg <?= empty($m['is_read']) ? 'unread' : '' ?>">
  <div class="row"><div><b><?= e($m['name']) ?></b> <small><?= e($m['email']) ?> · <?= e($m['created_at']) ?></small></div>
  <div class="actions">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_read"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><input type="hidden" name="tab" value="messages"><button class="ghost" type="submit"><?= empty($m['is_read']) ? 'Mark read' : 'Mark unread' ?></button></form>
    <?= del_form('message', (int)$m['id'], 'messages') ?></div></div>
  <p><?= nl2br(e($m['message'])) ?></p>
</div>
<?php endforeach; if (!$messages): ?><p class="muted">No messages yet.</p><?php endif; ?>

<?php elseif ($tab === 'projects'): ?>
<div class="table-wrap"><table><tr><th>Image</th><th>Title</th><th>Category</th><th></th></tr>
<?php foreach ($projects as $p): ?>
<tr><td><img class="thumb" src="../<?= e($p['image']) ?>" alt=""></td><td><?= e($p['title']) ?></td><td><?= e($p['category']) ?></td>
<td class="actions"><a class="ghost" href="index.php?tab=projects&amp;edit=<?= (int)$p['id'] ?>">Edit</a><?= del_form('project', (int)$p['id'], 'projects') ?></td></tr>
<?php endforeach; ?></table></div>
<form method="post" enctype="multipart/form-data" class="card form"><?= csrf_field() ?>
<input type="hidden" name="action" value="save_project"><input type="hidden" name="tab" value="projects"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<h3><?= $edit ? 'Edit project' : 'Add project' ?></h3>
<label>Title<input name="title" required maxlength="150" value="<?= v($edit, 'title') ?>"></label>
<label>Category<select name="category"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>" <?= ($edit['category'] ?? '') === $c ? 'selected' : '' ?>><?= e(ucfirst($c)) ?></option><?php endforeach; ?></select></label>
<label>Description<textarea name="description" rows="3" required maxlength="1000"><?= v($edit, 'description') ?></textarea></label>
<label>Technology<input name="technology" required maxlength="150" value="<?= v($edit, 'technology') ?>"></label>
<label>Image <?= $edit ? '(leave empty to keep the current one)' : '' ?> · JPG, PNG, WEBP or GIF, max 2 MB
<input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif" <?= $edit ? '' : 'required' ?>></label>
<div class="actions"><button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Add project' ?></button><?php if ($edit): ?><a class="ghost" href="index.php?tab=projects">Cancel</a><?php endif; ?></div>
</form>

<?php elseif ($tab === 'skills'): ?>
<div class="table-wrap"><table><tr><th>Icon</th><th>Name</th><th>Description</th><th></th></tr>
<?php foreach ($skills as $s): ?>
<tr><td><?= e($s['icon']) ?></td><td><?= e($s['name']) ?></td><td><?= e($s['description']) ?></td>
<td class="actions"><a class="ghost" href="index.php?tab=skills&amp;edit=<?= (int)$s['id'] ?>">Edit</a><?= del_form('skill', (int)$s['id'], 'skills') ?></td></tr>
<?php endforeach; ?></table></div>
<form method="post" class="card form"><?= csrf_field() ?>
<input type="hidden" name="action" value="save_skill"><input type="hidden" name="tab" value="skills"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<h3><?= $edit ? 'Edit skill' : 'Add skill' ?></h3>
<label>Emoji icon<input name="icon" required maxlength="10" value="<?= v($edit, 'icon') ?>"></label>
<label>Name<input name="name" required maxlength="100" value="<?= v($edit, 'name') ?>"></label>
<label>Description<input name="description" required maxlength="255" value="<?= v($edit, 'description') ?>"></label>
<div class="actions"><button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Add skill' ?></button><?php if ($edit): ?><a class="ghost" href="index.php?tab=skills">Cancel</a><?php endif; ?></div>
</form>

<?php elseif ($tab === 'resume'): ?>
<div class="table-wrap"><table><tr><th>Label</th><th>Title</th><th></th></tr>
<?php foreach ($resume as $r): ?>
<tr><td><?= e($r['label']) ?></td><td><?= e($r['title']) ?></td>
<td class="actions"><a class="ghost" href="index.php?tab=resume&amp;edit=<?= (int)$r['id'] ?>">Edit</a><?= del_form('resume', (int)$r['id'], 'resume') ?></td></tr>
<?php endforeach; ?></table></div>
<form method="post" class="card form"><?= csrf_field() ?>
<input type="hidden" name="action" value="save_resume"><input type="hidden" name="tab" value="resume"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<h3><?= $edit ? 'Edit resume item' : 'Add resume item' ?></h3>
<label>Label (e.g. Education)<input name="label" required maxlength="50" value="<?= v($edit, 'label') ?>"></label>
<label>Title<input name="title" required maxlength="150" value="<?= v($edit, 'title') ?>"></label>
<label>Description<textarea name="description" rows="3" required maxlength="1000"><?= v($edit, 'description') ?></textarea></label>
<div class="actions"><button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Add resume item' ?></button><?php if ($edit): ?><a class="ghost" href="index.php?tab=resume">Cancel</a><?php endif; ?></div>
</form>

<?php else: ?>
<form method="post" class="card form narrow"><?= csrf_field() ?>
<input type="hidden" name="action" value="change_password"><input type="hidden" name="tab" value="account">
<h3>Change password</h3>
<label>Current password<input type="password" name="current" required autocomplete="current-password"></label>
<label>New password (12+ characters)<input type="password" name="new" required minlength="12" autocomplete="new-password"></label>
<label>Confirm new password<input type="password" name="confirm" required minlength="12" autocomplete="new-password"></label>
<button class="btn" type="submit">Update password</button>
<p class="muted">You are logged out automatically after 30 minutes of inactivity.</p>
</form>
<?php endif; ?>
</main></body></html>
