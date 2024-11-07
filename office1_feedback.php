<?php
// Database connection for Office 1
$host = 'localhost';
$db = 'feedback_system'; // Database for Office 1
$user = 'root'; // replace with your DB username
$pass = ''; // replace with your DB password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all feedback from the database
$feedbacks = [];
$result = $conn->query("SELECT * FROM feedback");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

// Calculate the average rating
$averageRating = 0;
$totalRatings = count($feedbacks);
if ($totalRatings > 0) {
    $sumRatings = array_sum(array_column($feedbacks, 'rating'));
    $averageRating = $sumRatings / $totalRatings;
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback for Office 1</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .filter-section {
            margin: 20px 0;
        }

        .filter-section select {
            padding: 9px;
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .back-button button {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .back-button button:hover {
            background-color: #e53935;
        }

        .ratings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .ratings-table th, .ratings-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .ratings-table th {
            background-color: #f2f2f2;
        }

        .average-rating {
            margin-top: 20px;
            font-weight: bold;
        }

        .data-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            margin-top: 20px;
        }

        .no-data {
            font-weight: bold;
            color: #f44336;
        }
    </style>
</head>
<body>

<div class="back-button">
    <button onclick="goBack()">← Back to Office Selection</button>
</div>

<script>
    function goBack() {
        window.location.href = 'admin.html';
    }
</script>

<h1>Feedback for Office 1</h1>

<!-- Date Filter Section -->
<div class="filter-section">
    <label for="yearSelect">Choose Year:</label>
    <select id="yearSelect" onchange="handleYearChange()">
        <option value="">--Select Year--</option>
        <script>
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= 2000; year--) {
                document.write(`<option value="${year}">${year}</option>`);
            }
        </script>
    </select>
    
    <label for="monthSelect">Choose Month:</label>
    <select id="monthSelect" onchange="handleMonthChange()" disabled>
        <option value="">--Select Month--</option>
        <option value="0">January</option>
        <option value="1">February</option>
        <option value="2">March</option>
        <option value="3">April</option>
        <option value="4">May</option>
        <option value="5">June</option>
        <option value="6">July</option>
        <option value="7">August</option>
        <option value="8">September</option>
        <option value="9">October</option>
        <option value="10">November</option>
        <option value="11">December</option>
    </select>

    <label for="daySelect">Choose Day:</label>
    <select id="daySelect" onchange="filterRatings()" disabled>
        <option value="">--Select Day--</option>
        <script>
            for (let i = 1; i <= 31; i++) {
                document.write(`<option value="${i}">${i}</option>`);
            }
        </script>
    </select>
</div>

<!-- Data Container for Ratings -->
<div class="data-container">
    <table class="ratings-table" id="ratingsTable">
        <thead>
            <tr>
                <th>Rating</th>
                <th>Comment/Feedback</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td><?php echo $feedback['rating']; ?></td>
                    <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($feedback['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="average-rating" id="averageRating">Average Rating: <?php echo $totalRatings > 0 ? number_format($averageRating, 2) : '--'; ?></div>

<script>
    const ratingsTable = document.getElementById("ratingsTable").getElementsByTagName("tbody")[0];
    const averageRatingDiv = document.getElementById("averageRating");

    // Function to handle year change
    function handleYearChange() {
        const year = document.getElementById("yearSelect").value;
        const monthSelect = document.getElementById("monthSelect");
        const daySelect = document.getElementById("daySelect");

        monthSelect.disabled = !year;
        daySelect.disabled = true;
        daySelect.value = "";
        filterRatings();
    }

    // Function to handle month change
    function handleMonthChange() {
        const year = document.getElementById("yearSelect").value;
        const monthSelect = document.getElementById("monthSelect");
        const daySelect = document.getElementById("daySelect");

        daySelect.disabled = !(year && monthSelect.value);
        filterRatings();
    }

    // Filter and display ratings based on filters
    function filterRatings() {
        const year = document.getElementById("yearSelect").value;
        const month = document.getElementById("monthSelect").value;
        const day = document.getElementById("daySelect").value;

        ratingsTable.innerHTML = "";

        const filteredRatings = <?php echo json_encode($feedbacks); ?>.filter(rating => {
            const ratingDate = new Date(rating.created_at);
            return (
                (!year || ratingDate.getFullYear().toString() === year) &&
                (!month || ratingDate.getMonth().toString() === month) &&
                (!day || ratingDate.getDate().toString() === day)
            );
        });

        if (filteredRatings.length > 0) {
            filteredRatings.forEach(rating => {
                const newRow = ratingsTable.insertRow();
                newRow.innerHTML = `<td>${rating.rating}</td><td>${rating.feedback_text}</td><td>${new Date(rating.created_at).toLocaleDateString()}</td>`;
            });

            // Calculate average rating for filtered data
            const sumFilteredRatings = filteredRatings.reduce((sum, rating) => sum + rating.rating, 0);
            const average = sumFilteredRatings / filteredRatings.length;
            averageRatingDiv.innerText = `Average Rating: ${average.toFixed(2)}`;
        } else {
            averageRatingDiv.innerText = "Average Rating: --";
            ratingsTable.innerHTML = "<tr><td colspan='3' class='no-data'>No data available for selected filters.</td></tr>";
        }
    }
</script>

</body>
</html>
