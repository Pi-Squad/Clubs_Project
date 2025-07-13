<?php
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: admin_clubs.php');
    exit();
}

// Récupérer le club à modifier
$stmt = $pdo->prepare("SELECT * FROM club WHERE id = ?");
$stmt->execute([$id]);
$club = $stmt->fetch();

if (!$club) {
    header('Location: admin_clubs.php');
    exit();
}

// Récupérer les domaines
$domaines = $pdo->query("SELECT * FROM Domain ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $domain_id = intval($_POST['domain_id'] ?? 0);

    if ($nom === '' || $domain_id <= 0) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("UPDATE club SET nom = ?, Domain_id = ? WHERE id = ?");
        $stmt->execute([$nom, $domain_id, $id]);
        header('Location: admin_clubs.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier un club</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            background-color: #fff;
            padding: 20px 25px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        label {
            display: block;
            margin-bottom: 12px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #218838;
        }
        a.cancel-btn {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        a.cancel-btn:hover {
            background-color: #ddd;
        }
        p.error {
            color: #dc3545;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Modifier un club</h1>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? $club['nom']) ?>">

        <label for="domain_id">Domaine :</label>
        <select id="domain_id" name="domain_id" required>
            <option value="">-- Choisir un domaine --</option>
            <?php foreach ($domaines as $d): ?>
                <?php
                $selected = '';
                if (isset($_POST['domain_id'])) {
                    $selected = ($_POST['domain_id'] == $d['id']) ? 'selected' : '';
                } else {
                    $selected = ($club['Domain_id'] == $d['id']) ? 'selected' : '';
                }
                ?>
                <option value="<?= $d['id'] ?>" <?= $selected ?>>
                    <?= htmlspecialchars($d['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Modifier</button>
        <a href="admin_clubs.php" class="cancel-btn">Annuler</a>
    </form>
</body>
</html>

