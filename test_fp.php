<?php

try {
    $dbPath = __DIR__ . '/database.sqlite';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы если не существует
    $pdo->exec("CREATE TABLE IF NOT EXISTS matches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        home TEXT,
        away TEXT,
        score TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Обработка DELETE запроса
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents("php://input"), $deleteParams);
        $id = $deleteParams['id'] ?? null;
        
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM matches WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => "Match #{$id} deleted"]);
            } else {
                echo json_encode(['success' => false, 'message' => "Match #{$id} not found"]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID parameter required']);
        }
        exit;
    }

    // Обработка PATCH запроса
    if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        parse_str(file_get_contents("php://input"), $patchParams);
        $id = $patchParams['id'] ?? null;
        
        if ($id) {
            // Собираем поля для обновления
            $updateFields = [];
            $updateData = [':id' => $id];
            
            if (isset($patchParams['home'])) {
                $updateFields[] = 'home = :home';
                $updateData[':home'] = $patchParams['home'];
            }
            if (isset($patchParams['away'])) {
                $updateFields[] = 'away = :away';
                $updateData[':away'] = $patchParams['away'];
            }
            if (isset($patchParams['score'])) {
                $updateFields[] = 'score = :score';
                $updateData[':score'] = $patchParams['score'];
            }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE matches SET " . implode(', ', $updateFields) . " WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($updateData);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => "Match #{$id} updated"]);
                } else {
                    echo json_encode(['success' => false, 'message' => "Match #{$id} not found or no changes made"]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID parameter required']);
        }
        exit;
    }

    // Добавление тестовой записи (только для GET запросов)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("INSERT INTO matches (home, away, score) VALUES (:home, :away, :score)");
        $stmt->execute([':home' => 'Team A', ':away' => 'Team B', ':score' => '0:0']);

        // Вывод всех записей
        $rows = $pdo->query("SELECT * FROM matches ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Matches (sqlite)</h3>";
        echo "<p><strong>Endpoints:</strong></p>";
        echo "<ul>";
        echo "<li><strong>DELETE:</strong> Send DELETE request with parameter 'id'</li>";
        echo "<li><strong>PATCH:</strong> Send PATCH request with parameter 'id' and fields to update (home, away, score)</li>";
        echo "</ul>";
        echo "<ul>";
        foreach ($rows as $r) {
            echo "<li>#{$r['id']} — {$r['home']} vs {$r['away']} — score: {$r['score']} ({$r['created_at']})</li>";
        }
        echo "</ul>";
    }

} catch (PDOException $e) {
    echo "DB Error: " . htmlspecialchars($e->getMessage());
}