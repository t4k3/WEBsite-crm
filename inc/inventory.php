<?php
// Magazzino spara palloni (nuove + usate/ricondizionate).
// Quantità e storico movimenti; le specifiche stanno in product.php per modello.
require_once __DIR__ . '/db.php';

function inv_cfg(): array {
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/product.php';
    }
    return $cfg;
}

// Specifiche di un modello (velocità/altezza/batteria). Array vuoto se ignoto.
function inv_models(): array {
    return inv_cfg()['models'] ?? [];
}

function inv_model_specs(string $model): array {
    return inv_models()[$model] ?? ['speed' => '', 'height' => '', 'battery' => ''];
}

// Colori disponibili: ['Nome' => '#hex', ...]
function inv_colors(): array {
    return inv_cfg()['inventory_colors'] ?? [];
}

function inv_color_hex(string $color): string {
    return inv_colors()[$color] ?? '#9ca3af';
}

// Stati possibili di una riga: valore salvato => etichetta mostrata.
function inv_statuses(): array {
    return [
        'disponibile'     => 'Disponibile',
        'non_disponibile' => 'Non disponibile',
        'prenotata'       => 'Prenotata',
        'in_trattativa'   => 'In trattativa',
    ];
}

function inv_status_label(string $s): string {
    return inv_statuses()[$s] ?? $s;
}

// Elenco righe, opzionalmente filtrato per condizione ('nuova' | 'usata').
function inv_list(?string $condition = null): array {
    if ($condition !== null) {
        // Lotti (senza cliente) prima, poi le righe dei singoli pezzi impegnati/venduti.
        $st = db()->prepare(
            'SELECT * FROM inventory_items WHERE item_condition = ? ORDER BY (deal_id IS NOT NULL), model DESC, color ASC'
        );
        $st->execute([$condition]);
        return $st->fetchAll();
    }
    return db()->query(
        'SELECT * FROM inventory_items ORDER BY item_condition ASC, model DESC, color ASC'
    )->fetchAll();
}

function inv_get(int $id): ?array {
    $st = db()->prepare('SELECT * FROM inventory_items WHERE id = ?');
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ?: null;
}

// Movimenti recenti di una riga (per lo storico).
function inv_movements(int $itemId, int $limit = 20): array {
    $st = db()->prepare(
        'SELECT * FROM inventory_movements WHERE item_id = ? ORDER BY created_at DESC, id DESC LIMIT ?'
    );
    $st->bindValue(1, $itemId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

// Totale pezzi disponibili (righe con status 'disponibile').
// Tollera la tabella mancante (0) così la dashboard non si rompe se la
// migrazione sql/inventory.sql non è ancora stata eseguita.
function inv_total_available(): int {
    try {
        return (int) db()->query(
            "SELECT COALESCE(SUM(quantity),0) FROM inventory_items WHERE status = 'disponibile'"
        )->fetchColumn();
    } catch (\Throwable $e) {
        return 0;
    }
}

// Normalizza un prezzo scritto a mano ("3.500,00" o "3500") → float|null.
function inv_parse_price(string $raw): ?float {
    $raw = trim($raw);
    if ($raw === '') return null;
    $raw = str_replace(['.', ' '], '', $raw); // toglie separatore migliaia
    $raw = str_replace(',', '.', $raw);
    return is_numeric($raw) ? (float) $raw : null;
}

function inv_create(array $d): int {
    $st = db()->prepare(
        'INSERT INTO inventory_items (item_condition, model, color, quantity, price, machine_warranty, battery_warranty, status, notes)
         VALUES (?,?,?,?,?,?,?,?,?)'
    );
    $st->execute([
        $d['item_condition'],
        $d['model'],
        $d['color'],
        (int) $d['quantity'],
        $d['price'],
        ($d['machine_warranty'] ?? '') !== '' ? $d['machine_warranty'] : null,
        ($d['battery_warranty'] ?? '') !== '' ? $d['battery_warranty'] : null,
        $d['status'] ?? 'disponibile',
        ($d['notes'] ?? '') !== '' ? $d['notes'] : null,
    ]);
    return (int) db()->lastInsertId();
}

// Aggiorna i campi anagrafici (NON la quantità: quella passa da inv_set_quantity).
// deal_id = trattativa collegata (in trattativa / prenotata con); null = nessuno.
function inv_update(int $id, array $d): void {
    $st = db()->prepare(
        'UPDATE inventory_items SET model=?, color=?, price=?, machine_warranty=?, battery_warranty=?, status=?, deal_id=?, notes=? WHERE id=?'
    );
    $st->execute([
        $d['model'],
        $d['color'],
        $d['price'],
        ($d['machine_warranty'] ?? '') !== '' ? $d['machine_warranty'] : null,
        ($d['battery_warranty'] ?? '') !== '' ? $d['battery_warranty'] : null,
        $d['status'],
        !empty($d['deal_id']) ? (int) $d['deal_id'] : null,
        ($d['notes'] ?? '') !== '' ? $d['notes'] : null,
        $id,
    ]);
}

// Elenco trattative per la tendina "Cliente" del magazzino.
function inv_deals_for_select(): array {
    return db()->query(
        'SELECT id, contact_name, company_name, status FROM deals ORDER BY created_at DESC'
    )->fetchAll();
}

// Vende UN pezzo della riga al cliente indicato: -1, movimento "Venduta" con
// deal_id, e se la quantità arriva a 0 mette lo stato "non_disponibile".
// Ritorna false se non c'è stock o la riga non esiste.
function inv_sell(int $id, ?int $dealId, ?string $by): bool {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT quantity FROM inventory_items WHERE id = ? FOR UPDATE');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) { $pdo->rollBack(); return false; }
        $newQty = (int) $row['quantity'] - 1;
        if ($newQty < 0) { $pdo->rollBack(); return false; }
        if ($newQty === 0) {
            $pdo->prepare("UPDATE inventory_items SET quantity = 0, status = 'non_disponibile' WHERE id = ?")->execute([$id]);
        } else {
            $pdo->prepare('UPDATE inventory_items SET quantity = ? WHERE id = ?')->execute([$newQty, $id]);
        }
        $pdo->prepare(
            'INSERT INTO inventory_movements (item_id, delta, reason, deal_id, changed_by) VALUES (?,?,?,?,?)'
        )->execute([$id, -1, 'Venduta', $dealId, $by]);
        $pdo->commit();
        return true;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

// Storico vendite di una riga: chi ha comprato e quando (per il magazzino).
function inv_sales(int $itemId): array {
    $st = db()->prepare(
        "SELECT m.created_at, m.deal_id, d.contact_name, d.company_name
         FROM inventory_movements m
         LEFT JOIN deals d ON d.id = m.deal_id
         WHERE m.item_id = ? AND m.reason = 'Venduta'
         ORDER BY m.created_at DESC"
    );
    $st->execute([$itemId]);
    return $st->fetchAll();
}

// Per la scheda trattativa: macchine collegate (in trattativa/prenotata), non ancora vendute.
function inv_deal_linked(int $dealId): array {
    $st = db()->prepare(
        "SELECT * FROM inventory_items WHERE deal_id = ? AND status <> 'non_disponibile' ORDER BY model DESC, color"
    );
    $st->execute([$dealId]);
    return $st->fetchAll();
}

// Tutte le vendite del magazzino (per la tabella riepilogativa "Vendute").
function inv_sales_all(): array {
    return db()->query(
        "SELECT m.created_at, m.deal_id, i.model, i.color, i.item_condition, i.price, i.currency,
                d.contact_name, d.company_name
         FROM inventory_movements m
         JOIN inventory_items i ON i.id = m.item_id
         LEFT JOIN deals d ON d.id = m.deal_id
         WHERE m.reason = 'Venduta'
         ORDER BY m.created_at DESC, m.id DESC"
    )->fetchAll();
}

// Per la scheda trattativa: macchine acquistate da questo cliente (dallo storico vendite).
function inv_deal_purchases(int $dealId): array {
    $st = db()->prepare(
        "SELECT m.created_at, i.model, i.color, i.item_condition
         FROM inventory_movements m
         JOIN inventory_items i ON i.id = m.item_id
         WHERE m.deal_id = ? AND m.reason = 'Venduta'
         ORDER BY m.created_at DESC"
    );
    $st->execute([$dealId]);
    return $st->fetchAll();
}

function inv_delete(int $id): void {
    db()->prepare('DELETE FROM inventory_items WHERE id = ?')->execute([$id]);
}

// True se la riga è un "lotto" di macchine nuove (nuova, senza cliente collegato):
// su queste i pulsanti generano una riga singola invece di agire direttamente.
function inv_is_pool(array $it): bool {
    return $it['item_condition'] === 'nuova' && empty($it['deal_id']);
}

// Copia i dati anagrafici dal lotto e crea una riga singola per un pezzo.
function inv_spawn_unit(PDO $pdo, array $pool, string $status, ?int $dealId, int $qty): int {
    $ins = $pdo->prepare(
        'INSERT INTO inventory_items (item_condition, model, color, quantity, price, currency, machine_warranty, battery_warranty, status, deal_id)
         VALUES (?,?,?,?,?,?,?,?,?,?)'
    );
    $ins->execute([
        $pool['item_condition'], $pool['model'], $pool['color'], $qty,
        $pool['price'], $pool['currency'], $pool['machine_warranty'], $pool['battery_warranty'],
        $status, $dealId,
    ]);
    return (int) $pdo->lastInsertId();
}

// Toglie un pezzo dal lotto e lo blocca al cliente. $sold: false = "in trattativa", true = "venduta".
// Ritorna false se il lotto è vuoto.
function inv_commit_from_pool(int $poolId, int $dealId, bool $sold, ?string $by): bool {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT * FROM inventory_items WHERE id = ? FOR UPDATE');
        $st->execute([$poolId]);
        $pool = $st->fetch();
        if (!$pool || (int) $pool['quantity'] < 1) { $pdo->rollBack(); return false; }
        // -1 dal lotto; se arriva a 0 lo segna non disponibile (conto fatto in PHP)
        $newQty = (int) $pool['quantity'] - 1;
        if ($newQty === 0) {
            $pdo->prepare("UPDATE inventory_items SET quantity = 0, status = 'non_disponibile' WHERE id = ?")->execute([$poolId]);
        } else {
            $pdo->prepare('UPDATE inventory_items SET quantity = ? WHERE id = ?')->execute([$newQty, $poolId]);
        }
        if ($sold) {
            $unitId = inv_spawn_unit($pdo, $pool, 'non_disponibile', $dealId, 0);
            $pdo->prepare('INSERT INTO inventory_movements (item_id, delta, reason, deal_id, changed_by) VALUES (?,?,?,?,?)')
                ->execute([$unitId, -1, 'Venduta', $dealId, $by]);
        } else {
            $unitId = inv_spawn_unit($pdo, $pool, 'in_trattativa', $dealId, 1);
            $pdo->prepare('INSERT INTO inventory_movements (item_id, delta, reason, deal_id, changed_by) VALUES (?,?,?,?,?)')
                ->execute([$poolId, -1, 'Impegnata (trattativa)', $dealId, $by]);
        }
        $pdo->commit();
        return true;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

// Rimette una riga singola (impegnata/venduta) nel lotto: +1 al lotto corrispondente
// (o ne crea uno) e cancella la riga singola. Per quando una trattativa salta.
function inv_return_to_pool(int $unitId, ?string $by): bool {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT * FROM inventory_items WHERE id = ? FOR UPDATE');
        $st->execute([$unitId]);
        $unit = $st->fetch();
        if (!$unit) { $pdo->rollBack(); return false; }
        $ps = $pdo->prepare(
            'SELECT id FROM inventory_items WHERE item_condition = ? AND model = ? AND color = ? AND deal_id IS NULL LIMIT 1'
        );
        $ps->execute([$unit['item_condition'], $unit['model'], $unit['color']]);
        $poolId = (int) $ps->fetchColumn();
        if ($poolId) {
            // +1 al lotto; se era non disponibile torna disponibile (nessuna IF su quantity)
            $pst = $pdo->prepare('SELECT status FROM inventory_items WHERE id = ? FOR UPDATE');
            $pst->execute([$poolId]);
            $poolStatus = $pst->fetchColumn();
            $newStatus = $poolStatus === 'non_disponibile' ? 'disponibile' : $poolStatus;
            $pdo->prepare('UPDATE inventory_items SET quantity = quantity + 1, status = ? WHERE id = ?')
                ->execute([$newStatus, $poolId]);
            $pdo->prepare('INSERT INTO inventory_movements (item_id, delta, reason, deal_id, changed_by) VALUES (?,?,?,?,?)')
                ->execute([$poolId, 1, 'Rientro nel lotto', null, $by]);
        } else {
            inv_spawn_unit($pdo, $unit, 'disponibile', null, 1);
        }
        $pdo->prepare('DELETE FROM inventory_items WHERE id = ?')->execute([$unitId]);
        $pdo->commit();
        return true;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

// Imposta direttamente la quantità (rettifica manuale dal magazzino).
// Registra il movimento (differenza rispetto a prima) per lo storico.
// Ritorna false se newQty < 0 o la riga non esiste.
function inv_set_quantity(int $id, int $newQty, ?string $by): bool {
    if ($newQty < 0) return false;
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT quantity FROM inventory_items WHERE id = ? FOR UPDATE');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) { $pdo->rollBack(); return false; }
        $old = (int) $row['quantity'];
        if ($old !== $newQty) {
            $pdo->prepare('UPDATE inventory_items SET quantity = ? WHERE id = ?')->execute([$newQty, $id]);
            $pdo->prepare(
                'INSERT INTO inventory_movements (item_id, delta, reason, deal_id, changed_by) VALUES (?,?,?,?,?)'
            )->execute([$id, $newQty - $old, 'Rettifica manuale', null, $by]);
        }
        $pdo->commit();
        return true;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

// Variazione di quantità con storico. delta negativo = uscita.
// Usata dall'auto-scalo alla conferma ordine (parte 2).
// Ritorna false (senza scrivere) se un'uscita porterebbe la quantità sotto zero.
function inv_adjust(int $id, int $delta, ?string $reason, ?int $dealId, ?string $by): bool {
    if ($delta === 0) return true;
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT quantity FROM inventory_items WHERE id = ? FOR UPDATE');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) { $pdo->rollBack(); return false; }
        $newQty = (int) $row['quantity'] + $delta;
        if ($newQty < 0) { $pdo->rollBack(); return false; }
        $pdo->prepare('UPDATE inventory_items SET quantity = ? WHERE id = ?')->execute([$newQty, $id]);
        $pdo->prepare(
            'INSERT INTO inventory_movements (item_id, delta, reason, deal_id, changed_by) VALUES (?,?,?,?,?)'
        )->execute([$id, $delta, ($reason ?? '') !== '' ? $reason : null, $dealId, $by]);
        $pdo->commit();
        return true;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}
