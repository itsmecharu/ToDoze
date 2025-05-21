<?php
  $current_page = basename($_SERVER['PHP_SELF']);

  $is_home_active = $current_page === 'dash.php';

  // If "team" is anywhere in the filename
  $is_team_active = strpos($current_page, 'team') !== false;

  // If "task" is in the filename but "team" is NOT
  $is_task_active = strpos($current_page, 'task') !== false && strpos($current_page, 'team') === false;

  $is_review_active = strpos($current_page, 'review') !== false;
?>


  
  <!-- sorting ends-->
  <div class="logo-container">
    <img src="img/logo.png" alt="App Logo" class="logo">
  </div>

<!-- navbar.php -->
<div class="l-navbar" id="navbar">
  <nav class="nav">
    <div class="nav__list">
      <a href="dash.php" class="nav__link"><ion-icon name="home-outline" class="nav__icon"></ion-icon><span class="nav__name">Home</span></a>
<a href="task.php" class="nav__link <?= $is_task_active ? 'active' : '' ?>">
  <ion-icon name="add-outline" class="nav__icon"></ion-icon>
  <span class="nav__name">Task</span>
</a>

<a href="team.php" class="nav__link <?= $is_team_active ? 'active' : '' ?>">
  <ion-icon name="people-outline" class="nav__icon"></ion-icon>
  <span class="nav__name">Team</span>
</a>
<br>
      
      <!-- Dropdown Section -->
     <a href="javascript:void(0)" class="nav__link nav__dropdown-btn">
  <ion-icon name="ellipsis-horizontal-outline" class="nav__icon"></ion-icon>
  <span class="nav__name">Others</span>
  <i class="nav__dropdown-icon fa fa-caret-down"></i>
</a>

        <div class="nav__dropdown-content">
          <a href="review.php" class="nav__link"><ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon><span class="nav__name" >Review</span></a>
          <a href="change_name.php" class="nav__link"><ion-icon name="person-circle-outline" class="nav__icon"></ion-icon><span class="nav__name">Name</span></a>
          <a href="change_password.php" class="nav__link"><ion-icon name="key-outline" class="nav__icon"></ion-icon><span class="nav__name">Password</span></a>
        </div>
      </div>

    
    <!-- Logout button centered and positioned 40px from bottom -->
    <div class="nav__logout-container">
      <a href="javascript:void(0)" onclick="confirmLogout(event)" class="nav__link logout">
        <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
        <span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span>
      </a>
    </div>
  </nav>
</div>


<style>
    button{
        
    }
    .nav__dropdown-content {
  display: none;
  flex-direction: column;
  padding-left: 1.5rem;
}

.nav__dropdown-content.show-dropdown {
  display: flex;
}
 
.rotate-caret {
  transform: rotate(180deg);
  transition: transform 0.3s ease;
}

</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const dropdownBtn = document.querySelector('.nav__dropdown-btn');
    const dropdownContent = document.querySelector('.nav__dropdown-content');
    const caretIcon = document.querySelector('.nav__dropdown-icon');

    dropdownBtn.addEventListener('click', function () {
      dropdownContent.classList.toggle('show-dropdown');

      // Optional: rotate caret icon
      caretIcon.classList.toggle('rotate-caret');
    });
  });
</script>

