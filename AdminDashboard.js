// adminDashboard.js

$(document).ready(function () {
  const formContainer = $("#taskFormContainer");
  const toggleCreateBtn = $("#toggleCreateBtn");
  const taskForm = $("#taskForm");
  const formTitle = $("#formTitle");
  const submitBtn = $("#submitBtn");
  const msgDiv = $("#message");
  const token = localStorage.getItem("token");

  if (!token) {
    alert("Unauthorized: No token found.");
    window.location.href = "index.html";
    return;
  }

  // Date Range Picker Initialization
  $('#dateRange').daterangepicker({
    autoUpdateInput: false,
    locale: {
      cancelLabel: 'Clear',
      applyLabel: 'Apply',
      format: 'YYYY-MM-DD',
      separator: ' to '
    },
    opens: 'right',
    ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 Days': [moment().subtract(6, 'days'), moment()],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
  });

  $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
  });

  $('#dateRange').on('cancel.daterangepicker', function () {
    $(this).val('');
  });

  // Filter tasks by date range
  $('#filterTasksBtn').on('click', function () {
    const range = $('#dateRange').val();
    if (!range) {
      alert("Please select a date range.");
      return;
    }

    const [start, end] = range.split(" to ");
    $('#filterTasksBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Filtering...');

    $.ajax({
      url: 'admin.php?action=filterTasks',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ start, end }),
      dataType: 'json',
      headers: {
        "Authorization": 'Bearer ' + token
      },
      success: function (response) {
        if (!response.success) {
          alert(response.message || "Failed to filter tasks");
          return;
        }
        updateTasksTable(response.tasks);
      },
      error: function (xhr) {
        console.error("Filter error:", xhr.responseText);
        alert("Error filtering tasks. See console for details.");
      },
      complete: function () {
        $('#filterTasksBtn').html('Filter Tasks');
      }
    });
  });

  // Update tasks table
  function updateTasksTable(tasks) {
    const tbody = $("table tbody");
    tbody.empty();

    if (tasks.length === 0) {
      tbody.append('<tr><td colspan="8" class="text-center">No tasks found for selected dates.</td></tr>');
      return;
    }

    tasks.forEach(function (task) {
      const overdue = (new Date(task.deadline) < new Date() && task.status !== 'Completed');
      const row = `
        <tr style="color: ${overdue ? 'red' : 'inherit'}">
          <td>${escapeHtml(task.title)}</td>
          <td>${escapeHtml(task.description)}</td>
          <td>${escapeHtml(task.priority)}</td>
          <td>${escapeHtml(task.status)}</td>
          <td>${escapeHtml(task.deadline)}</td>
          <td>${escapeHtml(task.department)}</td>
          <td>${escapeHtml(task.assignedName || '')}</td>
          <td>
            <button class="btn btn-sm btn-warning edit-task-btn" data-task-id="${task.id}">✏️</button>
            <button class="btn btn-sm btn-danger delete-task-btn" data-task-id="${task.id}">🗑️</button>
          </td>
        </tr>`;
      tbody.append(row);
    });
  }

  // HTML Escape
  function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // Toggle form mode
  toggleCreateBtn.on("click", function () {
    taskForm.trigger("reset");
    $("#task_id").val("");
    formContainer.data("mode", "create");
    formTitle.text("Create New Task");
    submitBtn.val("Create Task");
    const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
    taskModal.show();
    const offset = formContainer.offset();
    if (offset) {
      $("html, body").animate({ scrollTop: offset.top }, 500);
    }
  });

  // Department dropdown change
  $("#departmentSelect").on("change", function () {
    const dept = $(this).val();
    const assignedSelect = $("#assigned_toSelect");
    assignedSelect.html("<option>Loading...</option>");

    $.getJSON("get_users_by_department.php", { department: dept }, function (data) {
      assignedSelect.empty();
      if (data.length === 0) {
        assignedSelect.append("<option value=''>No users found</option>");
      } else {
        $.each(data, function (i, user) {
          assignedSelect.append(`<option value="${user.id}">${user.firstName}</option>`);
        });
      }

      $("#departmentSelect").trigger("loadedUsers");
    }).fail(function () {
      assignedSelect.html("<option>Error loading users</option>");
    });
  });

  // Initial load of users
  $("#departmentSelect").val("").trigger("change");

  // Handle form submission
  taskForm.on("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const isEdit = formContainer.data("mode") === "edit";
    const actionUrl = isEdit ? "admin.php?action=updateTask" : "admin.php?action=createTask";

    $.ajax({
      url: actionUrl,
      method: "POST",
      data: formData,
      contentType: false,
      processData: false,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Authorization": `Bearer ${token}`
      },
      success: function (data) {
        const message = data.message || (data.success ? "Task saved successfully." : "Something went wrong.");
        msgDiv.html(`<p style="color:${data.success ? 'green' : 'red'};">${message}</p>`);
        if (data.success) {
          taskForm.trigger("reset");
          $("#taskModal").modal("hide");
          location.reload();
        }
      },
      error: function (err) {
        console.error("Form error:", err);
      }
    });
  });

  // Edit task handler
  $(document).on("click", ".edit-task-btn", function () {
    const taskId = $(this).data("task-id");
    $.ajax({
      url: `admin.php?action=getTask&task_id=${taskId}`,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Authorization": `Bearer ${token}`
      },
      success: function (task) {
        if (task && task.id) {
          $("#task_id").val(task.id);
          $("#title").val(task.title);
          $("#description").val(task.description);
          $("#priority").val(task.priority);
          $("#status").val(task.status);
          $("#deadline").val(task.deadline);
          $("#department").val(task.department);

          $("#departmentSelect").on("loadedUsers", function onLoaded() {
            $("#assigned_toSelect").val(task.assigned_to);
            $("#departmentSelect").off("loadedUsers", onLoaded);
          });

          $("#departmentSelect").val(task.department).trigger("change");

          formContainer.data("mode", "edit");
          formTitle.text("Edit Task");
          submitBtn.val("Update Task");
          const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
          taskModal.show();
          const offset = formContainer.offset();
          if (offset) {
            $("html, body").animate({ scrollTop: offset.top }, 500);
          }
        } else {
          alert("Failed to load task.");
        }
      },
      error: function (err) {
        console.error("Fetch error:", err);
      }
    });
  });

  // Delete task handler
  $(document).on("click", ".delete-task-btn", function () {
    if (!confirm("Are you sure you want to delete this task?")) return;
    const taskId = $(this).data("task-id");

    $.ajax({
      url: "admin.php?action=deleteTask",
      method: "POST",
      data: { task_id: taskId },
      headers: {
        "Authorization": `Bearer ${token}`
      },
      success: function () {
        location.reload();
      },
      error: function () {
        alert("Failed to delete task.");
      }
    });
  });
});
