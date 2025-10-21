<?php include(TEMPLATE_PATH . "/include/header.php"); ?>

<h2>Edit Task</h2>

<form method="post" action="admin.php?action=updateTask">
  <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">

  <ul>
    <li>
      <label for="title">Title:</label>
      <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
    </li>
    <li>
      <label for="description">Description:</label>
      <textarea name="description" required><?= htmlspecialchars($task['description']) ?></textarea>
    </li>
    <li>
      <label for="priority">Priority:</label>
      <select name="priority" required>
        <option value="Low" <?= $task['priority'] == 'Low' ? 'selected' : '' ?>>Low</option>
        <option value="Medium" <?= $task['priority'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
        <option value="High" <?= $task['priority'] == 'High' ? 'selected' : '' ?>>High</option>
      </select>
    </li>
    <li>
      <label for="status">Status:</label>
      <select name="status" required>
        <option value="To Do" <?= $task['status'] == 'To Do' ? 'selected' : '' ?>>To Do</option>
        <option value="In Progress" <?= $task['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="Completed" <?= $task['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
      </select>
    </li>
    <li>
      <label for="deadline">Deadline:</label>
      <input type="date" name="deadline" value="<?= $task['deadline'] ?>" required>
    </li>
  </ul>

  <div class="buttons">
    <input type="submit" name="updateTask" value="Update Task">
    <a href="admin.php?action=dashboard" style="margin-left:10px;">Cancel</a>
  </div>
</form>

<?php include(TEMPLATE_PATH . "/include/footer.php"); ?>
