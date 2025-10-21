<?php include(TEMPLATE_PATH . "/include/header.php"); ?> 
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="templates/include/Dashboard.js"></script>

<div class="container my-5">
  <div class="row">
    <div class="col text-center">
      <h2 class="mb-3">Legal Dashboard</h2>
      <p>Welcome, <?php echo htmlspecialchars($username); ?>! This is the dashboard for the Legal department.</p>
    </div>
  </div>

  <?php
  try {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "SELECT t.*, u.firstName AS assignedName
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.department = 'legal'
            ORDER BY t.deadline ASC";
    $st = $conn->prepare($sql);
    $st->execute();
    $tasks = $st->fetchAll();
    $conn = null;

    $events = [];
    foreach ($tasks as $task) {
      $events[] = [
        'id' => $task['id'],
        'title' => $task['title'] . " (" . $task['status'] . ")",
        'start' => $task['deadline'],
        'allDay' => true
      ];
    }
    $eventsJson = json_encode($events);
  } catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching tasks: " . $e->getMessage() . "</div>";
    $tasks = [];
    $eventsJson = json_encode([]);
  }
  ?>

  <div class="row">
    <div class="col-12">
      <h3 class="mt-5">Task Calendar</h3>
      <div id="calendar"></div>
    </div>
  </div>

  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,listWeek'
        },
        events: <?php echo $eventsJson; ?>,
        eventClick: function(info) {
          const taskId = info.event.id;
          fetch(`admin.php?action=getTask&task_id=${taskId}`, {
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
          })
          .then(res => res.json())
          .then(task => {
            if (task && task.id) {
              $("#task_id").val(task.id);
              $("#title").val(task.title);
              $("#description").val(task.description);
              $("#priority").val(task.priority);
              $("#status").val(task.status);
              $("#deadline").val(task.deadline);
              $("#department").val(task.department);

              $("#assigned_to").val(task.assigned_to);

              $("#taskModalLabel").text("Edit Task");
              $("#submitBtn").val("Update Task");

              const modal = new bootstrap.Modal(document.getElementById('taskModal'));
              modal.show();
            } else {
              alert("Failed to load task details.");
            }
          })
          .catch(err => {
            console.error("Fetch error:", err);
            alert("Error loading task.");
          });
        }
      });
      calendar.render();
    });

    $(document).ready(function() {
      $("#toggleCreateBtn").on("click", function() {
        // Reset form for create mode
        $("#taskForm")[0].reset();
        $("#task_id").val("");
        $("#taskModalLabel").text("Create New Task");
        $("#submitBtn").val("Create Task");
      });
    });
  </script>
  <style>
    #calendar {
      max-width: 100%;
      margin: 40px auto;
      background: #fff;
      padding: 10px;
      border-radius: 8px;
      min-height: 600px;
    }
    .fc-list-event-title a {
    color: navy !important;
    }
    
  </style>

  <hr class="my-5">

  <div class="row justify-content-center mb-4">
    <div class="col-auto">
      <button id="toggleCreateBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#taskModal">➕ Create New Task</button>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="taskModalLabel">Create New Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form id="taskForm" method="post">
            <input type="hidden" name="task_id" id="task_id">
            <input type="hidden" name="department" id="department" value="legal">

            <div id="message"></div>

            <div class="mb-3">
              <label for="title" class="form-label">Task Title</label>
              <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
              <label for="priority" class="form-label">Priority</label>
              <select name="priority" id="priority" class="form-select">
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select" required>
                <option value="To Do">To Do</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="deadline" class="form-label">Deadline</label>
              <input type="date" name="deadline" id="deadline" class="form-control" required>
            </div>
            <div class="mb-4">
              <label for="assigned_to" class="form-label">Assign To</label>
              <select name="assigned_to" id="assigned_to" class="form-select" required>
                <?php
                $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
                $sql = "SELECT id, firstName FROM users WHERE department = 'legal'";
                $st = $conn->prepare($sql);
                $st->execute();
                $users = $st->fetchAll();
                foreach ($users as $user) {
                  echo "<option value=\"{$user['id']}\">{$user['firstName']}</option>";
                }
                $conn = null;
                ?>
              </select>
            </div>
            <button type="button" id="submitBtn" class="btn btn-primary w-100">Create Task</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-5">
    <div class="col-12"> 
      <h3 class="mb-3">Existing Tasks</h3>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Title</th>
              <th>Description</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Deadline</th>
              <th>Assigned To</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($tasks)): ?>
              <tr><td colspan="7" class="text-center">No tasks found.</td></tr>
            <?php else: ?>
              <?php foreach ($tasks as $task): ?>
                <tr style="color: <?= (strtotime($task['deadline']) < time() && $task['status'] != 'Completed') ? 'red' : '#ffffff' ?>">
                  <td><?= htmlspecialchars($task['title']) ?></td>
                  <td><?= htmlspecialchars($task['description']) ?></td>
                  <td><?= $task['priority'] ?></td>
                  <td><?= $task['status'] ?></td>
                  <td><?= $task['deadline'] ?></td>
                  <td><?= htmlspecialchars($task['assignedName']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning edit-task-btn" data-task-id="<?= $task['id'] ?>">✏️</button>
                    <button class="btn btn-sm btn-danger delete-task-btn" data-task-id="<?= $task['id'] ?>">🗑️</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteLabel">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body" id="confirmDeleteMessage">
          Are you sure you want to delete this task? <br>This action cannot be undone.
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
        </div>

      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col text-center">
      <a href="admin.php?action=logoutUser" class="btn btn-outline-secondary">Logout</a>
    </div>
  </div>
</div>

<?php include(TEMPLATE_PATH . "/include/footer.php"); ?>
