<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toggle Password Visibility with Image</title>
    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Form container */
        .form-wrapper {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Input field styling */
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        /* Password visibility icon styling */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper img {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 20px;  /* Adjust image size if necessary */
        }

        /* Submit button styling */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="form-wrapper">
        <h3>Sign In</h3>
        <form method="POST" action="#">
            <input type="password" name="userpassword" id="password" placeholder="Enter your password" required>
            <div class="password-wrapper">
                <!-- Initial hide image -->
                <img src="img/hide.png" id="toggle-icon" alt="Hide Icon" onclick="togglePassword()">
            </div>
            <button type="submit">Sign In</button>
        </form>
    </div>

    <!-- JavaScript to toggle password visibility -->
    <script>
        function togglePassword() {
    var passwordField = document.getElementById('password');
    var icon = document.getElementById('toggle-icon');  // Get the image element

    // Check if the password is currently hidden or visible
    if (passwordField.type === "password") {
        passwordField.type = "text";  // Show password
        icon.src = "img/show.png";  // Change icon to show image
    } else {
        passwordField.type = "password";  // Hide password
        icon.src = "img/hide.png";  // Change icon to hide image
    }
}

    </script>

</body>
</html>

<!--  -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task Form</title>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Hide the form container by default */
        .container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7); /* Start smaller for pop effect */
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            border-radius: 10px;
            opacity: 0; /* Start invisible */
            transition: opacity 0.3s ease, transform 0.3s ease; /* Smooth transition */
            width: 500px; /* Smaller container width */
        }

        /* Show the form container with pop effect */
        .container.active {
            display: block;
            opacity: 1; /* Fully visible */
            transform: translate(-50%, -50%) scale(1); /* Pop to full size */
        }

        /* Style the form box */
        .box {
            width: 100%; /* Take full width of the container */
            text-align: center;
        }

        /* Style the form inputs */
        .add-task-form input,
        .add-task-form select,
        .add-task-form button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Style the submit button */
        .add-task-form button {
            background-color: #45a049;
            color: white;
            border: none;
            cursor: pointer; 
        }

        .add-task-form button:hover {
            background-color:rgb(106, 223, 112);
        }

        /* Overlay background when form is open */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            transition: opacity 0.3s ease; /* Smooth overlay transition */
        }

        /* Show the overlay */
        .overlay.active {
            display: block;
            opacity: 1; /* Fully visible */
        }

        /* Style the "Add Task" button */
        #addTaskButton {
            position: fixed; /* Fixed position */
            bottom: 20px; /* Distance from the bottom */
            right: 20px; /* Distance from the right */
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1001; /* Ensure it's above other elements */
        }

        #addTaskButton:hover {
            background-color: #0056b3;
        }

        /* Style for date, time, and reminder buttons */
        .input-button {
            width: 100%;
            height: 60%;
            padding: 10px;
            margin: 10px 0;
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            text-align: left;
        }

        .input-button:hover {
            background-color: #ddd;
        }

        /* Hide the actual input fields */
        .hidden-input {
            display: none;
        }

        /* Add Task icon styling */
        .add-task-icon {
            margin-right: 8px; /* Space between icon and text */
        }

        /* Flexbox for date and time buttons */
        .date-time-container {
            display: flex;
            gap: 10px; /* Space between date and time buttons */
        }

        .date-time-container .input-button {
            flex: 1; /* Equal width for both buttons */
        }
    </style>
</head>
<body>
    <!-- Button to show the form with an icon -->
    <button id="addTaskButton">
        <i class="fas fa-plus-circle add-task-icon"></i> Add Task
    </button>

    <!-- Form Container -->
    <div class="container" id="formContainer">
        <div class="box">
            <h2>Add Task Here</h2>
            <form class="add-task-form">
                <!-- <label for="taskname">Task Name:</label> -->
                <input type="text" id="taskname" name="taskname" placeholder="Add task here" required>

                <!-- <label for="taskDescription">Task Description:</label> -->
                <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description..." style="height: 80px;">

                <!-- Date and Time Buttons in the same line -->
                <!-- <label>Due Date and Time:</label> -->
                <div class="date-time-container">
                    <button type="button" class="input-button" id="dateButton">Select Date 📅</button>
                    <button type="button" class="input-button" id="timeButton">Select Time ⏲️</button>
                </div>
                <input type="date" id="taskdate" name="taskdate" class="hidden-input">
                <input type="time" id="tasktime" name="tasktime" class="hidden-input">

                <!-- Reminder Button and Dropdown -->
                <!-- <label>Set Reminder:</label> -->
                <button type="button" class="input-button" id="reminderButton">Set Reminder 🔔</button>
                <select id="reminder" name="reminder_percentage" class="hidden-input">
                    <option value="" disabled selected>Set Reminder Here </option>
                    <option value="50">50% (Halfway to Due Date)</option>
                    <option value="75">75% (Closer to Due Date)</option>
                    <option value="90">90% (Near Due Date)</option>
                    <option value="100">100% (On Time)</option>
                </select>

                <button type="submit">Done</button>
            </form>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <script>
        // Get references to the button, form container, and overlay
        const addTaskButton = document.getElementById('addTaskButton');
        const formContainer = document.getElementById('formContainer');
        const overlay = document.getElementById('overlay');

        // Show the form container with pop effect
        addTaskButton.addEventListener('click', () => {
            formContainer.classList.add('active');
            overlay.classList.add('active');
        });

        // Hide the form container when clicking outside of it (on the overlay)
        overlay.addEventListener('click', () => {
            formContainer.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Handle date button click
        const dateButton = document.getElementById('dateButton');
        const dateInput = document.getElementById('taskdate');

        dateButton.addEventListener('click', () => {
            dateInput.click(); // Trigger the date input
        });

        // Handle time button click
        const timeButton = document.getElementById('timeButton');
        const timeInput = document.getElementById('tasktime');

        timeButton.addEventListener('click', () => {
            timeInput.click(); // Trigger the time input
        });

        // Handle reminder button click
        const reminderButton = document.getElementById('reminderButton');
        const reminderSelect = document.getElementById('reminder');

        reminderButton.addEventListener('click', () => {
            reminderSelect.style.display = 'block'; // Show the dropdown
            reminderSelect.focus(); // Focus on the dropdown
        });

        // Hide the dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (event.target !== reminderButton && event.target !== reminderSelect) {
                reminderSelect.style.display = 'none';
            }
        });
    </script>
</body>
</html>