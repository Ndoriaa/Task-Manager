$(document).ready(function() {
  const $taskForm = $("#taskForm");
  const $submitBtn = $("#submitBtn");
  const $msgDiv = $("#message");
  const taskModalEl = document.getElementById('taskModal');
  const taskModal = new bootstrap.Modal(taskModalEl);

  const confirmDeleteModalEl = document.getElementById('confirmDeleteModal');
  const confirmDeleteModal = new bootstrap.Modal(confirmDeleteModalEl);
  let deleteTaskId = null;

  // Create or Edit Task submit
  $submitBtn.on("click", function(e) {
    e.preventDefault();

    const formData = new FormData($taskForm[0]);
    const isEdit = $("#task_id").val() !== "";
    const actionUrl = isEdit ? "admin.php?action=updateTask" : "admin.php?action=createTask";

    $.ajax({
      url: actionUrl,
      method: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(data) {
        if (data.success) {
          $msgDiv.html(`<p style="color: green;">${data.message}</p>`);

          const newRow = `
            <tr style="color: ${($("#deadline").val() && new Date($("#deadline").val()) < new Date() && $("#status").val() !== 'Completed') ? 'red' : '#ffffff'}">
              <td>${$("#title").val()}</td>
              <td>${$("#description").val()}</td>
              <td>${$("#priority").val()}</td>
              <td>${$("#status").val()}</td>
              <td>${$("#deadline").val()}</td>
              <td>${$("#assigned_to option:selected").text()}</td>
              <td>
                <button class="btn btn-sm btn-warning edit-task-btn" data-task-id="${data.id}">✏️</button>
                <button class="btn btn-sm btn-danger delete-task-btn" data-task-id="${data.id}">🗑️</button>
              </td>
            </tr>
          `;

          if (isEdit) {
            $(`button.edit-task-btn[data-task-id="${data.id}"]`).closest("tr").replaceWith(newRow);
          } else {
            $("table tbody").append(newRow);
          }

          $taskForm[0].reset();
          $("#task_id").val("");
          $("#taskModalLabel").text("Create New Task");
          $submitBtn.text("Create Task");
          taskModal.hide();
        } else {
          $msgDiv.html(`<p style="color: red;">${data.message}</p>`);
        }
      },
      error: function(xhr) {
        $msgDiv.html(`<p style="color: red;">An error occurred. Please try again.</p>`);
        console.error(xhr);
      }
    });
  });

  // 👇 Use delegated event for Edit button
  $(document).on("click", ".edit-task-btn", function() {
    const taskId = $(this).data("taskId");

    $.getJSON(`admin.php?action=getTask&task_id=${taskId}`, function(task) {
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
        $submitBtn.text("Update Task");

        $msgDiv.html("");
        taskModal.show();
      } else {
        alert("Failed to load task details.");
      }
    }).fail(function(xhr) {
      alert("Error fetching task details.");
      console.error(xhr);
    });
  });

  // 👇 Use delegated event for Delete button
  $(document).on("click", ".delete-task-btn", function() {
    deleteTaskId = $(this).data("taskId");
    confirmDeleteModal.show();
  });

  // Confirm delete
  $("#confirmDeleteBtn").on("click", function() {
    if (!deleteTaskId) return;

    $.ajax({
      url: "admin.php?action=deleteTask",
      method: "POST",
      data: { task_id: deleteTaskId },
      dataType: "json",
      success: function(data) {
        if (data.success) {
          $(`button.delete-task-btn[data-task-id="${deleteTaskId}"]`).closest("tr").remove();
          deleteTaskId = null;
          confirmDeleteModal.hide();
        } else {
          alert(data.message || "Failed to delete task.");
        }
      },
      error: function(xhr) {
        alert("Error deleting task. Please try again.");
        console.error(xhr);
      }
    });
  });

  // Create button click (reset modal)
  $("#toggleCreateBtn").on("click", function() {
    $taskForm[0].reset();
    $("#task_id").val("");
    $("#taskModalLabel").text("Create New Task");
    $submitBtn.text("Create Task");
    $msgDiv.html("");
  });
});
