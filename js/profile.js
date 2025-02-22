document.getElementById('nav-toggle').addEventListener('click', function() {
    const navbar = document.querySelector('.l-navbar');
    const body = document.getElementById('body-pd');
    navbar.classList.toggle('collapsed');
    body.classList.toggle('collapsed');
});