<?php
class Task {

  private static function connect() {
    $host = "localhost";
    $dbname = "task_manager";
    $username = "root";
    $password = "dpHcTuw-R2uQP0]P";

    try {
      return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    } catch (PDOException $e) {
      die("DB connection failed: " . $e->getMessage());
    }
  }

  public static function getFilteredTasks($start, $end) {
    $pdo = self::connect();
    $stmt = $pdo->prepare("SELECT tasks.*, users.firstName AS assignedName 
                           FROM tasks 
                           LEFT JOIN users ON tasks.assigned_to = users.id
                           WHERE deadline BETWEEN :start AND :end
                           ORDER BY deadline ASC");
    $stmt->execute([
      ':start' => $start,
      ':end' => $end
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function convertToCalendarEvents($tasks) {
    $events = [];
    foreach ($tasks as $task) {
      $events[] = [
        'id' => $task['id'],
        'title' => $task['title'],
        'start' => $task['deadline'],
        'color' => $task['status'] === 'Completed' ? '#28a745' : '#dc3545'
      ];
    }
    return $events;
  }
}
?>
