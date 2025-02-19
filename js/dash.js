
/*===== EXPANDER MENU  =====*/ 
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
  
  
  /*===== COLLAPSE MENU  =====*/ 
  const linkCollapse = document.getElementsByClassName('collapse__link')
  var i
  
  for(i=0;i<linkCollapse.length;i++){
    linkCollapse[i].addEventListener('click', function(){
      const collapseMenu = this.nextElementSibling
      collapseMenu.classList.toggle('showCollapse')
  
      const rotate = collapseMenu.previousElementSibling
      rotate.classList.toggle('rotate')
    })
  }
  
  /*add task js*/
  // Task Data Storage
let tasks = [];

// Add Task Function
function addTask() {
    const taskDescription = document.getElementById('taskDescription').value;
    const setDate = document.getElementById('setDate').value;
    const dueDate = document.getElementById('dueDate').value;

    if (taskDescription === '' || setDate === '' || dueDate === '') {
        alert('Please fill out all fields.');
        return;
    }

    tasks.push({
        description: taskDescription,
        setDate,
        dueDate,
        status: 'Pending'
    });

    displayTasks();
    updateTaskSummary();
    updateGraph();

    document.getElementById('taskDescription').value = '';
    document.getElementById('setDate').value = '';
    document.getElementById('dueDate').value = '';
}

// Display Tasks
function displayTasks() {
    const taskList = document.getElementById('taskList');
    taskList.innerHTML = '';
    tasks.forEach((task, index) => {
        const taskItem = document.createElement('div');
        taskItem.classList.add('task');
        taskItem.innerHTML = `
            <h3>${task.description}</h3>
            <p><strong>Set Date:</strong> ${task.setDate}</p>
            <p><strong>Due Date:</strong> ${task.dueDate}</p>
            <p><strong>Status:</strong> 
                <select class="status-select" onchange="updateTaskStatus(${index}, this)">
                    <option value="Pending" ${task.status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="In Progress" ${task.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                    <option value="Completed" ${task.status === 'Completed' ? 'selected' : ''}>Completed</option>
                </select>
            </p>
            <button onclick="removeTask(${index})">Remove Task</button>
        `;
        taskList.appendChild(taskItem);
    });
}

// Update Task Status
function updateTaskStatus(index, select) {
    tasks[index].status = select.value;
    updateTaskSummary();
    updateGraph();
}

// Remove Task
function removeTask(index) {
    tasks.splice(index, 1);
    displayTasks();
    updateTaskSummary();
    updateGraph();
}

// Update Task Summary
function updateTaskSummary() {
    const totalTasks = tasks.length;
    const pendingTasks = tasks.filter(task => task.status === 'Pending').length;
    const completedTasks = tasks.filter(task => task.status === 'Completed').length;

    document.getElementById('totalTasks').textContent = `${completedTasks + pendingTasks}/${totalTasks}`;
    document.getElementById('pendingTasks').textContent = pendingTasks;
    document.getElementById('completedTasks').textContent = completedTasks;
}

// Graph Initialization
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

// Update Graph
function updateGraph() {
    let counts = { 'Pending': 0, 'In Progress': 0, 'Completed': 0 };
    tasks.forEach(task => counts[task.status]++);
    taskChart.data.datasets[0].data = [counts['Pending'], counts['In Progress'], counts['Completed']];
    taskChart.update();
}

 /* review js*/
 // Get all star labels
const stars = document.querySelectorAll('.rating-stars label');

// Add click event to each star
stars.forEach((star, index) => {
    star.addEventListener('click', () => {
        // Calculate the star rating based on the clicked star's index
        const rating = stars.length - index;
        alert(`You rated this ${rating} star(s)!`);
    });
});
 
  // toggle js
  // Toggle Task Form Visibility
function toggleTaskForm() {
    const form = document.getElementById('addTaskForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
