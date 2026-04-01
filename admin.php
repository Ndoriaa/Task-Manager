<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

ini_set("display_errors", 1);
error_reporting(E_ALL);

require("config.php");
require_once("classes/Tasks.php");
session_start();

// Constants
const JWT_ISSUER = 'yourdomain.com';
const JWT_EXPIRY = 3600; // 1 hour

$action = $_GET['action'] ?? "";
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function generateToken($user) {
    $payload = [
        'iss' => JWT_ISSUER,
        'iat' => time(),
        'exp' => time() + JWT_EXPIRY,
        'username' => $user->firstName,
        'department' => strtolower($user->department),
        'is_admin' => $user->is_admin
    ];
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function decodeToken($authHeader) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $jwt = $matches[1];
        try {
            return (array) JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }
    return null;
}

function verifyToken() {
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $token = decodeToken($headers['authorization'] ?? '');
    if ($token) return $token;

    if (isset($_SESSION['username'])) {
        return [
            'username' => $_SESSION['username'],
            'department' => $_SESSION['department'],
            'is_admin' => $_SESSION['is_admin']
        ];
    }
    return false;
}

function requireAuth() {
    $token = verifyToken();
    if (!$token) {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }
    return $token;
}

function refreshToken() {
    $tokenData = requireAuth();

    $user = new stdClass();
    $user->firstName = $tokenData['username'];
    $user->department = $tokenData['department'];
    $user->is_admin = $tokenData['is_admin']; 
    $newToken = generateToken($user);

    echo json_encode(["success" => true, "token" => $newToken]);
    exit;
}

switch ($action) {
    case 'registerUser': registerUser(); break;
    case 'loginUser': loginUser(); break;
    case 'logoutUser': logoutUser(); break;
    case 'dashboard': dashboard(); break;
    case 'createTask': createTask(); break;
    case 'editTask': editTask(); break;
    case 'deleteTask': deleteTask(); break;
    case 'updateTask': updateTask(); break;
    case 'refreshToken': refreshToken(); break;
    case 'filterTasks': filterTasks(); break;
    case 'saveScheduler': handleSaveScheduler();break;
    case'saveDiary': handleSaveDiary();break;
    case 'getScheduler': handleGetScheduler(); break;
    case 'getDiary': handleGetDiary(); break;
    case 'viewDiaryAndSchedule': viewDiaryAndSchedule(); break;
    case 'getTask': if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['task_id'])) getTask($_GET['task_id']); break;
    default: showLoginForm();
}

function registerUser() {
    global $isAjax;

    if (isset($_POST['register'])) {
        $user = new User;
        $user->storeFormValues($_POST);
        $user->passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT); // Use password not passwordHash

        // If user is admin, optionally set department as "admin"
        if ($user->is_admin) {
            $user->department = "admin";
        }

        $user->insert();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => true,
                "message" => "User registered successfully"
            ]);
            exit;
        } else {
            header("Location: admin.php?action=loginUser&status=registered");
            exit;
        }
    } else {
        require(TEMPLATE_PATH . "/admin/registerForm.php");
    }
}


function loginUser() {
   $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = User::authenticate($_POST['firstName'], $_POST['password']);

    if ($user) {
      $jwt = generateToken($user);

      // ✅ Save token & user info in session
      $_SESSION['token'] = $jwt;
      $_SESSION['username'] = $user->firstName;
      $_SESSION['department'] = strtolower($user->department);
      $_SESSION['is_admin'] = $user->is_admin; 

      if ($isAjax) {
        echo json_encode([
          "success" => true,
          "message" => "Login successful",
          "token" => $jwt
        ]);
        return;
      }

      // Redirect if not Ajax
      header("Location: admin.php?action=dashboard");
      exit();

    } else {
      if ($isAjax) {
        echo json_encode([
          "success" => false,
          "message" => "Invalid credentials"
        ]);
        return;
      }

      require TEMPLATE_PATH . "/admin/loginForm.php";
    }

  } else {
    require TEMPLATE_PATH . "/admin/loginForm.php";
  }
}






function logoutUser() {
  unset($_SESSION['username']);
  header("Location: admin.php?action=loginUser");
}

function showLoginForm() {
  require TEMPLATE_PATH . "/admin/loginForm.php";
}

function dashboard() {
  // ✅ Use session values instead of JWT headers
  $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    if ($isAjax) {
      http_response_code(401);
      echo json_encode(["success" => false, "message" => "Unauthorized or expired token"]);
      exit;
    } else {
      // Redirect to login page if session expired
      header("Location: admin.php?action=loginUser&error=sessionExpired");
      exit;
    }
  }


  $username = $_SESSION['username'];
  $department = $_SESSION['department'];
  $isAdmin = $_SESSION['is_admin'];

  // If admin, show admin dashboard
  if ($isAdmin) {
    require(TEMPLATE_PATH . "/departments/adminDashboard.php");
    return;
  }

  switch ($department) {
    case 'finance':
      require(TEMPLATE_PATH . "/departments/financeDashboard.php");
      break;
    case 'it':
      require(TEMPLATE_PATH . "/departments/itDashboard.php");
      break;
    case 'hr':
      require(TEMPLATE_PATH . "/departments/hrDashboard.php");
      break;
    case 'marketing':
      require(TEMPLATE_PATH . "/departments/marketingDashboard.php");
      break;
    case 'operations':
      require(TEMPLATE_PATH . "/departments/operationsDashboard.php");
      break;
    case 'legal':
      require(TEMPLATE_PATH . "/departments/legalDashboard.php");
      break;
    case 'procurement':
      require(TEMPLATE_PATH . "/departments/procurementDashboard.php");
      break;
    case 'customerservice':
      require(TEMPLATE_PATH . "/departments/customerserviceDashboard.php");
      break;
    default:
      require(TEMPLATE_PATH . "/departments/defaultDashboard.php");
  }
}

function getUserIdByName($username) {
  $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
  $sql = "SELECT id FROM users WHERE CONCAT(firstName, ' ', secondName) = :name";
  $st = $conn->prepare($sql);
  $st->bindValue(":name", $username, PDO::PARAM_STR);
  $st->execute();
  $user = $st->fetch();
  $conn = null;
  return $user ? $user['id'] : null;
}

function createTask() {
  $tokenData = requireAuth();
  if (!$tokenData) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized or expired token"]);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
      $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
      $sql = "INSERT INTO tasks (title, description, priority, status, deadline, department, assigned_to, email, created_by) 
              VALUES (:title, :description, :priority,:status, :deadline, :department, :assigned_to, email, :created_by)";
      $st = $conn->prepare($sql);
      $st->bindValue(":title", $_POST['title'], PDO::PARAM_STR);
      $st->bindValue(":description", $_POST['description'], PDO::PARAM_STR);
      $st->bindValue(":priority", $_POST['priority'], PDO::PARAM_STR);
      $st->bindValue(":status", $_POST['status'], PDO::PARAM_STR);
      $st->bindValue(":deadline", $_POST['deadline'], PDO::PARAM_STR);
      $st->bindValue(":department", $_POST['department'], PDO::PARAM_STR);
      $st->bindValue(":email", $_POST['email'], PDO::PARAM_STR);
      $st->bindValue(":assigned_to", $_POST['assigned_to'], PDO::PARAM_INT);
      $st->bindValue(":created_by", getUserIdByName($tokenData['username']), PDO::PARAM_INT);
      $st->execute();
      $conn = null;
      header("Content-Type: application/json");
      echo json_encode(["success" => true, "message" => "Task created successfully."]);
      exit;

    } catch (PDOException $e) {
      echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
      exit;
    }
  }
}


function editTask() {
  $tokenData = requireAuth();
  if (!$tokenData) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
  }

  if (!isset($_GET['task_id']) || !is_numeric($_GET['task_id'])) {
    echo "<p class='errorMessage'>Task ID is required.</p>";
    return;
  }

 $taskId = (int)($_GET['task_id'] ?? 0);
if (!$taskId) {
    echo json_encode(["success" => false, "message" => "Missing task ID"]);
    exit;
}

try {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "SELECT * FROM tasks WHERE id = :id";
    $st = $conn->prepare($sql);
    $st->bindValue(":id", $taskId, PDO::PARAM_INT);
    $st->execute();
    $task = $st->fetch(PDO::FETCH_ASSOC);
    $conn = null;

    header("Content-Type: application/json");

    if ($task) {
        echo json_encode($task);
    } else {
        echo json_encode(["success" => false, "message" => "Task not found."]);
    }
    exit;
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB error: " . $e->getMessage()]);
    exit;
}


  require TEMPLATE_PATH . "/departments/editTaskForm.php";
}



function deleteTask() {
  if (!isset($_POST['task_id'])) {
    echo json_encode(["success" => false, "message" => "Task ID is required."]);
    exit;
  }

  try {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "DELETE FROM tasks WHERE id = :id";
    $st = $conn->prepare($sql);
    $st->bindValue(":id", $_POST['task_id'], PDO::PARAM_INT);
    $st->execute();
    $conn = null;

    header("Content-Type: application/json");
    echo json_encode(["success" => true, "message" => "Task deleted successfully."]);
    exit;
  } catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Delete failed: " . $e->getMessage()]);
    exit;
  }
}



function getTask($taskId) {
  $tokenData = requireAuth();
  if (!$tokenData) {
    http_response_code(401);
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
  }

  try {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "SELECT * FROM tasks WHERE id = :id";
    $st = $conn->prepare($sql);
    $st->bindValue(":id", $taskId, PDO::PARAM_INT);
    $st->execute();
    $task = $st->fetch(PDO::FETCH_ASSOC);

    header("Content-Type: application/json");

    if ($task) {
      echo json_encode($task);
    } else {
      echo json_encode(["success" => false, "message" => "Task not found."]);
    }
    exit;
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB error: " . $e->getMessage()]);
    exit;
  }
}


function updateTask() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    try {
      $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
      $sql = "UPDATE tasks SET 
                title = :title,
                description = :description,
                priority = :priority,
                status = :status,
                deadline = :deadline,
                department=:department,
                assigned_to = :assigned_to
              WHERE id = :id";
      $st = $conn->prepare($sql);
      $st->bindValue(":id", $_POST['task_id'], PDO::PARAM_INT);
      $st->bindValue(":title", $_POST['title'], PDO::PARAM_STR);
      $st->bindValue(":description", $_POST['description'], PDO::PARAM_STR);
      $st->bindValue(":priority", $_POST['priority'], PDO::PARAM_STR);
      $st->bindValue(":status", $_POST['status'], PDO::PARAM_STR);
      $st->bindValue(":deadline", $_POST['deadline'], PDO::PARAM_STR);
      $st->bindValue(":department", $_POST['department'], PDO::PARAM_STR);
      $st->bindValue(":assigned_to", $_POST['assigned_to'], PDO::PARAM_INT);
      $st->execute();

      echo json_encode(["success" => true, "message" => "Task updated successfully"]);
    } catch (PDOException $e) {
      echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
    }
  } else {
    echo json_encode(["success" => false, "message" => "Missing task ID."]);
  }
  exit;

  if (isset($_GET['action']) && $_GET['action'] == 'filterTasks') {
  $start = $_POST['start'] ?? null;
  $end = $_POST['end'] ?? null;

  try {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "SELECT t.*, u.firstName AS assignedName
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.department = 'customerservice'";

    if ($start && $end) {
      $sql .= " AND DATE(t.deadline) BETWEEN :start AND :end";
    }

    $sql .= " ORDER BY t.deadline ASC";
    $st = $conn->prepare($sql);

    if ($start && $end) {
      $st->bindValue(":start", $start);
      $st->bindValue(":end", $end);
    }

    $st->execute();
    $tasks = $st->fetchAll();

    $events = [];
    foreach ($tasks as $task) {
      $events[] = [
        'id' => $task['id'],
        'title' => $task['title'] . " (" . $task['status'] . ")",
        'start' => $task['deadline'],
        'allDay' => true
      ];
    }

    echo json_encode([
      'success' => true,
      'tasks' => $tasks,
      'events' => $events
    ]);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
  }
  exit();
}
}

function filterTasks() {
    //Verify token first
   $tokenData = requireAuth();
    if (!$tokenData) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    $start = $input['start'] ?? $_POST['start'] ?? null;
    $end = $input['end'] ?? $_POST['end'] ?? null;

    if (!$start || !$end) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing date range']);
        exit;
    }

    try {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT t.*, u.firstName AS assignedName
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE DATE(t.deadline) BETWEEN :start AND :end
                ORDER BY t.deadline ASC";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":start", $start);
        $st->bindValue(":end", $end);
        $st->execute();
        $tasks = $st->fetchAll();

        $events = [];
        foreach ($tasks as $task) {
            $events[] = [
                'id' => $task['id'],
                'title' => $task['title'] . " (" . $task['status'] . ")",
                'start' => $task['deadline'],
                'allDay' => true,
                //'color' => getStatusColor($task['status'])
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'tasks' => $tasks,
            'events' => $events
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Helper function for status colors
// function getStatusColor($status) {
//     switch ($status) {
//         case 'Completed': return '#28a745';
//         case 'In Progress': return '#ffc107';
//         default: return '#dc3545';
//     }
// }

function handleSaveScheduler() {
    $tokenData = requireAuth();  // Still require auth
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $date = $data['date'] ?? null;
    $slots = $data['slots'] ?? [];

    if (!$date || !is_array($slots)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        return;
    }

    try {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO scheduler (scheduled_date, hour, task_description) VALUES (:date, :hour, :desc)");

        foreach ($slots as $hour => $desc) {
            if (trim($desc) !== '') {
                $stmt->execute([
                    ':date' => $date,
                    ':hour' => intval($hour),
                    ':desc' => $desc
                ]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Schedule saved.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
}

function handleSaveDiary() {
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    $date = $data['date'] ?? null;
    $entry = $data['entry'] ?? null;

    if (!$date || !$entry) {
        echo json_encode(['success' => false, 'message' => 'Missing date or entry.']);
        return;
    }

    try {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $stmt = $conn->prepare("INSERT INTO diary (entry_date, entry) VALUES (:date, :entry)");
        $stmt->execute([':date' => $date, ':entry' => $entry]);

        echo json_encode(['success' => true, 'message' => 'Diary entry saved.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetDiary() {
    $tokenData = requireAuth();
    header('Content-Type: application/json');

    $date = $_GET['date'] ?? null;

    if (!$date) {
        echo json_encode(['success' => false, 'message' => 'Missing date.']);
        exit;
    }

    try {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $stmt = $conn->prepare("SELECT entry FROM diary WHERE entry_date = :date LIMIT 1");
        $stmt->execute([':date' => $date]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'entry' => $entry ? $entry['entry'] : ''
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function handleGetScheduler() {
    $tokenData = requireAuth();
    header('Content-Type: application/json');

    $date = $_GET['date'] ?? null;

    if (!$date) {
        echo json_encode(['success' => false, 'message' => 'Missing date.']);
        exit;
    }

    try {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $stmt = $conn->prepare("SELECT hour, task_description FROM scheduler WHERE scheduled_date = :date ORDER BY hour ASC");
        $stmt->execute([':date' => $date]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'entries' => $entries]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function viewDiaryAndSchedule() {
    require TEMPLATE_PATH . "/departments/viewDiaryAndSchedule.php";
}

