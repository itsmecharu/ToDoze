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


document.addEventListener("DOMContentLoaded", function() {
    let popup = document.querySelector(".popup");
    let overlay = document.createElement("div");
    overlay.className = "popup-overlay";
    document.body.appendChild(overlay);

    if (popup) {
        popup.style.display = "block";
        overlay.style.display = "block";

        setTimeout(() => {
            popup.style.display = "none";
            overlay.style.display = "none";
        }, 3000); // Hide after 3 seconds
    }
});

// Logout confirmation function
function confirmLogout(event) {
    event.preventDefault(); // Stop the default link behavior
    document.activeElement.blur(); // Remove focus from clicked element

    Swal.fire({
        title: 'Log out?',
        text: "Are you sure you want to log out?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Stay',
        backdrop: false,
        background: '#fff',
        allowOutsideClick: true,
        customClass: {
            popup: 'swal2-custom-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "logout.php?action=logout";
        } else if (result.dismiss === Swal.DismissReason.backdrop || result.dismiss === Swal.DismissReason.cancel) {
            console.log("Logout cancelled");
        }
    });
}

// Navigation active state
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav__link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
});


