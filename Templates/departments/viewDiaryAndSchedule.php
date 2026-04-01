<?php require_once("config.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Diary & Scheduler View</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4 text-start">
    <a href="/TaskManager/admin.php?action=dashboard" class="btn btn-secondary">
        ⬅️ Back to Home
    </a>
</div>
<div class="container mt-5">
    <h2 class="mb-4 text-center">📅 View Diary & Scheduled Tasks</h2>

    <div class="row mb-3">
        <div class="col-md-6 offset-md-3">
            <input type="date" id="selectedDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>

    <div class="row">
        <!-- Diary Section -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">📔 Diary Entry</div>
                <div class="card-body">
                    <p id="diaryDisplay" class="text-muted">Select a date to view the diary entry.</p>
                </div>
            </div>
        </div>

        <!-- Scheduler Section -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">🕒 Scheduled Tasks</div>
                <div class="card-body">
                    <div id="scheduleDisplay" class="text-muted">Select a date to view scheduled tasks.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
   function loadDiary(date) {
    $.get(`/TaskManager/admin.php?action=getDiary&date=${date}`, function(response) {
        if (response.success) {
            $("#diaryDisplay").text(response.entry || "No diary entry for this date.");
        } else {
            $("#diaryDisplay").text("Error loading diary entry.");
        }
    });
    }

    function loadScheduler(date) {
        $.get(`/TaskManager/admin.php?action=getScheduler&date=${date}`, function(response) {
            if (response.success) {
                let html = '';
                if (response.entries.length > 0) {
                    response.entries.forEach(slot => {
                        html += `<div><strong>${slot.hour}:00</strong> - ${slot.task_description}</div>`;
                    });
                } else {
                    html = "No scheduled tasks for this date.";
                }
                $("#scheduleDisplay").html(html);
            } else {
                $("#scheduleDisplay").text("Error loading scheduled tasks.");
            }
        });
    }



    // Load today's data on page load
    $(document).ready(function() {
        const today = $("#selectedDate").val();
        loadDiary(today);
        loadScheduler(today);

        // On date change
        $("#selectedDate").on("change", function() {
            const selected = $(this).val();
            loadDiary(selected);
            loadScheduler(selected);
        });
    });
</script>
</body>
</html>
