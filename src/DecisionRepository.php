<?php
declare(strict_types=1);

final class DecisionRepository
{
    public const SORTS = [
        'latest' => ['label' => 'Terbaru', 'sql' => 'buried_at DESC, id DESC'],
        'oldest' => ['label' => 'Terlama', 'sql' => 'buried_at ASC, id ASC'],
        'az'     => ['label' => 'A → Z',        'sql' => 'LOWER(title) ASC, id ASC'],
        'za'     => ['label' => 'Z → A',        'sql' => 'LOWER(title) DESC, id DESC'],
    ];

    public function __construct(private PDO $pdo) {}

    public function all(string $status, string $sort = 'latest'): array
    {
        $order = self::SORTS[$sort]['sql'] ?? self::SORTS['latest']['sql'];
        $stmt  = $this->pdo->prepare("SELECT * FROM decisions WHERE status = :status ORDER BY $order");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM decisions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function count(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM decisions WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $d): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO decisions (title, cause_of_death, category, born_on, died_on)
             VALUES (:title, :cause, :category, :born_on, :died_on)
             RETURNING id'
        );
        $stmt->execute([
            'title'    => $d['title'],
            'cause'    => $d['cause_of_death'],
            'category' => $d['category'],
            'born_on'  => $d['born_on'],
            'died_on'  => $d['died_on'],
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE decisions
                SET title = :title, cause_of_death = :cause, category = :category,
                    born_on = :born_on, died_on = :died_on, updated_at = NOW()
              WHERE id = :id'
        );
        $stmt->execute([
            'id'       => $id,
            'title'    => $d['title'],
            'cause'    => $d['cause_of_death'],
            'category' => $d['category'],
            'born_on'  => $d['born_on'],
            'died_on'  => $d['died_on'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function setStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE decisions
                SET status = :status,
                    resurrected_at = CASE WHEN :status2 = 'resurrected' THEN NOW() ELSE NULL END,
                    buried_at      = CASE WHEN :status3 = 'buried' THEN NOW() ELSE buried_at END,
                    died_on        = CASE WHEN :status4 = 'buried'
                                          THEN GREATEST(born_on, date_trunc('month', CURRENT_DATE)::date)
                                          ELSE died_on END,
                    updated_at     = NOW()
              WHERE id = :id"
        );
        $stmt->execute(['id' => $id, 'status' => $status, 'status2' => $status, 'status3' => $status, 'status4' => $status]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM decisions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
