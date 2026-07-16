<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
exigerConnexion();

$u = utilisateurCourant();
$admin = in_array(strtolower((string) ($u['role'] ?? '')), ['admin', 'superadmin', 'teamcoach'], true);

$pdo = portailDb();
$pdo->exec("CREATE TABLE IF NOT EXISTS portail_couts (
    app_id      VARCHAR(40) PRIMARY KEY,
    libelle     VARCHAR(120) NOT NULL,
    ordre       INT NOT NULL DEFAULT 0,
    hebergement DECIMAL(10,2) NOT NULL DEFAULT 0,
    api         DECIMAL(10,2) NOT NULL DEFAULT 0,
    note        VARCHAR(255) NOT NULL DEFAULT '',
    maj         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Amorçage de la liste des apps au premier lancement
$defaut = [
    ['portail', 'famiPortail (bureau + connexion)'],
    ['famicom', 'famiCom'],
    ['famirayon', 'famiRayon (IA)'],
    ['famidata', 'Famidata'],
    ['famibotanic', 'famiBotanic (IA)'],
    ['cloud', 'Espace Cloud'],
    ['famiformation', 'famiFormation (service séparé)'],
    ['mysql', 'Base de données MySQL'],
];
if ((int) $pdo->query("SELECT COUNT(*) FROM portail_couts")->fetchColumn() === 0) {
    $ins = $pdo->prepare("INSERT INTO portail_couts (app_id, libelle, ordre) VALUES (?, ?, ?)");
    foreach ($defaut as $i => $d) { $ins->execute([$d[0], $d[1], $i]); }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin && csrfValide($_POST['csrf'] ?? null)) {
    $nb = 0;
    $st = $pdo->prepare("UPDATE portail_couts SET hebergement=?, api=?, note=? WHERE app_id=?");
    foreach ($pdo->query("SELECT app_id FROM portail_couts")->fetchAll() as $r) {
        $id = $r['app_id'];
        $h = (float) str_replace([',', ' ', '€'], ['.', '', ''], (string) ($_POST['h_' . $id] ?? '0'));
        $a = (float) str_replace([',', ' ', '€'], ['.', '', ''], (string) ($_POST['a_' . $id] ?? '0'));
        $n = trim((string) ($_POST['n_' . $id] ?? ''));
        $st->execute([$h, $a, $n]);
        $nb++;
    }
    $message = 'Coûts mis à jour.';
}

$rows = $pdo->query("SELECT * FROM portail_couts ORDER BY ordre")->fetchAll();
$totH = 0; $totA = 0;
foreach ($rows as $r) { $totH += (float) $r['hebergement']; $totA += (float) $r['api']; }
$jeton = jetonCsrf();
function eur($v) { return number_format((float) $v, 2, ',', ' ') . ' €'; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paramètres — Coûts des applications</title>
<style>
  :root{--font:-apple-system,BlinkMacSystemFont,"Segoe UI",system-ui,Roboto,Helvetica,Arial,sans-serif;
    --bg:#0e1712;--panel:#131e18;--panel2:#18241d;--border:#243228;--ink:#e9f1eb;--soft:#94a89b;--faint:#66786d;--brand:#37c07a;--brand-deep:#1c6b41;}
  *{box-sizing:border-box;} body{margin:0;font-family:var(--font);background:var(--bg);color:var(--ink);-webkit-font-smoothing:antialiased;}
  .topbar{display:flex;align-items:center;gap:12px;padding:13px clamp(16px,4vw,28px);border-bottom:1px solid var(--border);}
  .retour{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;color:var(--soft);text-decoration:none;background:var(--panel2);border:1px solid var(--border);} .retour:hover{color:var(--ink);}
  .t{font-weight:700;font-size:16px;} .t .s{font-size:12px;font-weight:500;color:var(--faint);}
  .wrap{max-width:840px;margin:0 auto;padding:26px clamp(16px,4vw,28px) 70px;}
  h1{margin:0 0 4px;font-size:22px;} .sous{color:var(--soft);font-size:14px;margin-bottom:22px;}
  .bloc{background:var(--panel);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
  table{width:100%;border-collapse:collapse;}
  th{text-align:left;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--faint);padding:12px 16px;border-bottom:1px solid var(--border);}
  td{padding:10px 16px;border-bottom:1px solid var(--border);font-size:14px;} tr:last-child td{border-bottom:none;}
  .num{text-align:right;font-variant-numeric:tabular-nums;}
  input.c{width:100px;background:var(--panel2);border:1px solid var(--border);color:var(--ink);border-radius:9px;padding:8px 10px;font:inherit;font-size:13.5px;text-align:right;}
  input.n{width:100%;background:var(--panel2);border:1px solid var(--border);color:var(--ink);border-radius:9px;padding:8px 10px;font:inherit;font-size:13px;}
  input:focus{outline:none;border-color:var(--brand);}
  .tot{font-weight:800;} tr.total td{background:var(--panel2);font-weight:800;font-size:15px;}
  .btn{border:none;border-radius:11px;padding:11px 18px;font-weight:700;font-size:14px;cursor:pointer;background:linear-gradient(135deg,#48d089,var(--brand-deep));color:#06130c;}
  .msg{background:rgba(55,192,122,.14);border:1px solid var(--border);color:var(--brand);border-radius:11px;padding:10px 14px;font-weight:700;font-size:13.5px;margin-bottom:16px;}
  .ro{color:var(--soft);} .pied{color:var(--faint);font-size:12.5px;margin-top:16px;}
</style>
</head>
<body>
<div class="topbar">
  <a class="retour" href="index.php" title="Retour au bureau"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
  <div class="t">Paramètres<div class="s">Coût des applications</div></div>
</div>

<div class="wrap">
  <h1>Coût des applications</h1>
  <p class="sous">Combien chaque application nous coûte par mois (hébergement + API/IA).<?= $admin ? ' Modifiez les montants puis enregistrez.' : ' (Lecture seule — réservé aux administrateurs pour la modification.)' ?></p>

  <?php if ($message): ?><div class="msg">✓ <?= h($message) ?></div><?php endif; ?>

  <form method="POST">
    <input type="hidden" name="csrf" value="<?= h($jeton) ?>">
    <div class="bloc">
      <table>
        <thead><tr><th>Application</th><th class="num">Hébergement /mois</th><th class="num">API / IA /mois</th><th class="num">Total</th><th>Note</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $t = (float) $r['hebergement'] + (float) $r['api']; ?>
          <tr>
            <td><b><?= h($r['libelle']) ?></b></td>
            <?php if ($admin): ?>
              <td class="num"><input class="c" name="h_<?= h($r['app_id']) ?>" value="<?= number_format((float)$r['hebergement'],2,',','') ?>"></td>
              <td class="num"><input class="c" name="a_<?= h($r['app_id']) ?>" value="<?= number_format((float)$r['api'],2,',','') ?>"></td>
              <td class="num tot"><?= eur($t) ?></td>
              <td><input class="n" name="n_<?= h($r['app_id']) ?>" value="<?= h($r['note']) ?>" placeholder="ex. service Railway, API Anthropic…"></td>
            <?php else: ?>
              <td class="num ro"><?= eur($r['hebergement']) ?></td>
              <td class="num ro"><?= eur($r['api']) ?></td>
              <td class="num tot"><?= eur($t) ?></td>
              <td class="ro"><?= h($r['note']) ?></td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
          <tr class="total">
            <td>Total</td>
            <td class="num"><?= eur($totH) ?></td>
            <td class="num"><?= eur($totA) ?></td>
            <td class="num"><?= eur($totH + $totA) ?></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
    <?php if ($admin): ?><div style="margin-top:16px;"><button class="btn" type="submit">Enregistrer les coûts</button></div><?php endif; ?>
  </form>

  <p class="pied">💡 Ces montants sont saisis à la main (Railway et l'API Anthropic ne fournissent pas de total automatique ici). Mettez-les à jour selon vos factures.</p>
</div>
</body>
</html>
