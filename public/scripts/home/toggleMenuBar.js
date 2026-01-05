const toggleButton = document.querySelector('.menu-toggle');
const nav = document.querySelector('header nav');
const logoWrapper = document.querySelector('.logo-wrapper');

toggleButton.addEventListener('click', () => {
    nav.classList.toggle('active');
    logoWrapper.classList.toggle('active');
    toggleButton.classList.toggle('open');
});