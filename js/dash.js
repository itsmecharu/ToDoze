const showMenu = (toggleId, navbarId, bodyId) => {
  const toggle = document.getElementById(toggleId),
    navbar = document.getElementById(navbarId),
    bodypadding = document.getElementById(bodyId);

  if (toggle && navbar) {
    toggle.addEventListener('click', () => {
      navbar.classList.toggle('expander'); // Toggle expander class for navbar
      bodypadding.classList.toggle('body-pd'); // Toggle body-pd class for body
    });
  }
};
showMenu('nav-toggle', 'navbar', 'body-pd');
  
  /*===== LINK ACTIVE =====*/
  const linkColor = document.querySelectorAll('.nav__link');
  function colorLink() {
    linkColor.forEach(l => l.classList.remove('active')); // Remove active class from all links
    this.classList.add('active'); // Add active class to the clicked link
  }
  linkColor.forEach(l => l.addEventListener('click', colorLink));
  // Function to toggle the tick button
function toggleTick(button) {
    button.classList.toggle('ticked');
  }