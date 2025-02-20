const showMenu = (toggleId, navbarId, bodyId)=>{
    const toggle = document.getElementById(toggleId),
    navbar = document.getElementById(navbarId),
    bodypadding = document.getElementById(bodyId)
  
    if(toggle && navbar){
      toggle.addEventListener('click', ()=>{
        navbar.classList.toggle('expander')
  
        bodypadding.classList.toggle('body-pd')
      })
    }
  }
  showMenu('nav-toggle','navbar','body-pd')
  
  /*===== LINK ACTIVE  =====*/ 
  const linkColor = document.querySelectorAll('.nav__link')
  function colorLink(){
    linkColor.forEach(l=> l.classList.remove('active'))
    this.classList.add('active')
  }
  linkColor.forEach(l=> l.addEventListener('click', colorLink))
document.addEventListener('DOMContentLoaded', () => {
    let tasks = [];

    // Function to add a new task
    function addTask() {
        console.log("Add Task function called"); // Debugging line
 const taskName = document.getElementById('taskName').value;
        const description = document.getElementById('taskDescription').value;
        const setDate = document.getElementById('setDate').value;
        const dueDate = document.getElementById('dueDate').value;

        console.log("Input values:", { description, setDate, dueDate }); // Debugging line

        if (!taskName|| !description || !setDate || !dueDate) {
            alert('Please fill all fields!');
            return;
        }

        const newTask = {
            name: taskName,
            description,
            setDate,
            dueDate,
            status: 'Pending',
            subtasks: []
        };

        tasks.push(newTask);
        console.log("Task added:", newTask); // Debugging line
        console.log("All tasks:", tasks); // Debugging line

        displayTasks();
        updateTaskSummary();
        updateGraph();
        updateProgressBar();
        document.getElementById('taskName').value = '';
        document.getElementById('taskDescription').value = '';
        document.getElementById('setDate').value = '';
        document.getElementById('dueDate').value = '';
    }

    // Function to display all tasks
    function displayTasks() {
        console.log("Display Tasks function called"); // Debugging line

        const taskList = document.getElementById('taskList');
        taskList.innerHTML = ''; // Clear the task list

        tasks.forEach((task, index) => {
            const taskDiv = document.createElement('div');
            taskDiv.classList.add('task');

            taskDiv.innerHTML = `
                 <h3>${task.name}</h3>
                <input type="text" value="${task.description}" onblur="editTask(${index}, this)" />
                <p><strong>Set Date:</strong> ${task.setDate}</p>
                <p><strong>Due Date:</strong> ${task.dueDate}</p>
                <p><strong>Status:</strong> 
                    <select onchange="updateTaskStatus(${index}, this)">
                        <option value="Pending" ${task.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="In Progress" ${task.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                        <option value="Completed" ${task.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </p>

                <h4>Subtasks</h4>
                <div id="subtaskList${index}"></div>
                <input type="text" placeholder="Add subtask..." onkeypress="addSubtask(event, ${index})">
                
                <div class="task-actions">
                    <button onclick="removeTask(${index})">Remove Task</button>
                </div>
            `;

            taskList.appendChild(taskDiv);
            displaySubtasks(index); // Display subtasks for this task
        });
    }

    // Function to edit a task's description
    function editTask(index, input) {
        tasks[index].description = input.value;
        console.log("Task description updated:", tasks[index]); // Debugging line
    }

    // Function to update a task's status
    function updateTaskStatus(index, select) {
        tasks[index].status = select.value;
        console.log("Task status updated:", tasks[index]); // Debugging line
        updateTaskSummary();
        updateGraph();
        updateProgressBar();
    }

    // Function to remove a task
    function removeTask(index) {
        tasks.splice(index, 1);
        console.log("Task removed. Remaining tasks:", tasks); // Debugging line
        displayTasks();
        updateTaskSummary();
        updateGraph();
        updateProgressBar();
    }

    // Function to add a subtask
    function addSubtask(event, taskIndex) {
        if (event.key === "Enter") {
            const subtaskText = event.target.value;
            if (subtaskText.trim() !== "") {
                tasks[taskIndex].subtasks.push({ text: subtaskText, done: false });
                console.log("Subtask added:", tasks[taskIndex].subtasks); // Debugging line
                event.target.value = ""; // Clear the input field
                displaySubtasks(taskIndex); // Update the subtask list
                updateTaskStatusBasedOnSubtasks(taskIndex); // Update task status
            }
        }
    }

    // Function to display subtasks for a task
    function displaySubtasks(taskIndex) {
        const subtaskDiv = document.getElementById(`subtaskList${taskIndex}`);
        subtaskDiv.innerHTML = ""; // Clear the subtask list

        tasks[taskIndex].subtasks.forEach((subtask, subIndex) => {
            const subtaskItem = document.createElement('div');
            subtaskItem.classList.add('subtask');
            subtaskItem.innerHTML = `
                <input type="checkbox" onchange="toggleSubtask(${taskIndex}, ${subIndex})" ${subtask.done ? 'checked' : ''}>
                <span>${subtask.text}</span>
            `;
            subtaskDiv.appendChild(subtaskItem);
        });
    }

    // Function to toggle a subtask's done status
    function toggleSubtask(taskIndex, subIndex) {
        tasks[taskIndex].subtasks[subIndex].done = !tasks[taskIndex].subtasks[subIndex].done;
        console.log("Subtask toggled:", tasks[taskIndex].subtasks[subIndex]); // Debugging line
        updateTaskStatusBasedOnSubtasks(taskIndex); // Update task status
        updateProgressBar(); // Update progress bar
    }

    // Function to update task status based on subtasks
    function updateTaskStatusBasedOnSubtasks(taskIndex) {
        const task = tasks[taskIndex];
        const allSubtasksDone = task.subtasks.length > 0 && task.subtasks.every(subtask => subtask.done);
        const someSubtasksDone = task.subtasks.some(subtask => subtask.done);

        if (allSubtasksDone) {
            task.status = 'Completed';
        } else if (someSubtasksDone) {
            task.status = 'In Progress';
        } else {
            task.status = 'Pending';
        }

        console.log("Task status updated based on subtasks:", task); // Debugging line
        displayTasks();
        updateTaskSummary();
        updateGraph();
    }

    // Function to update the task summary
    function updateTaskSummary() {
        const totalTasks = tasks.length;
        const pendingTasks = tasks.filter(t => t.status === 'Pending').length;
        const completedTasks = tasks.filter(t => t.status === 'Completed').length;

        document.getElementById('totalTasks').textContent = totalTasks;
        document.getElementById('pendingTasks').textContent = pendingTasks;
        document.getElementById('completedTasks').textContent = completedTasks;

        console.log("Task summary updated:", { totalTasks, pendingTasks, completedTasks }); // Debugging line
    }

    // Function to update the progress bar
    function updateProgressBar() {
        const completedTasks = tasks.filter(t => t.status === 'Completed').length;
        const totalTasks = tasks.length;
        const progress = totalTasks > 0 ? (completedTasks / totalTasks) * 100 : 0;

        document.getElementById('progressBar').style.width = `${progress}%`;
        console.log("Progress bar updated:", progress); // Debugging line
    }

    // Initialize the task graph
    const ctx = document.getElementById('taskGraph').getContext('2d');
    let taskChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Completed'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#f1c40f', '#3498db', '#2ecc71']
            }]
        }
    });

    // Function to update the task graph
    function updateGraph() {
        const pendingTasks = tasks.filter(t => t.status === 'Pending').length;
        const inProgressTasks = tasks.filter(t => t.status === 'In Progress').length;
        const completedTasks = tasks.filter(t => t.status === 'Completed').length;

        taskChart.data.datasets[0].data = [pendingTasks, inProgressTasks, completedTasks];
        taskChart.update();
        console.log("Graph updated:", { pendingTasks, inProgressTasks, completedTasks }); // Debugging line
    }

    // Add event listener for the form submission
    const addTaskForm = document.querySelector('.add-task-form');
    addTaskForm.addEventListener('submit', (event) => {
        event.preventDefault();
        console.log("Form submitted"); // Debugging line
        addTask();
    });
});