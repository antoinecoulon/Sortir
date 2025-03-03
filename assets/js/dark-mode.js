// Initialisation du thème
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    document.getElementById('theme-toggle-light-icon').classList.remove('hidden');
    document.getElementById('theme-toggle-dark-icon').classList.add('hidden');
} else {
    document.documentElement.classList.remove('dark');
    document.getElementById('theme-toggle-light-icon').classList.add('hidden');
    document.getElementById('theme-toggle-dark-icon').classList.remove('hidden');
}

// Gestion du bouton de toggle
document.getElementById('theme-toggle').addEventListener('click', function() {
    document.getElementById('theme-toggle-dark-icon').classList.toggle('hidden');
    document.getElementById('theme-toggle-light-icon').classList.toggle('hidden');

    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        // localStorage.theme = "light";
        localStorage.setItem('color-theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        // localStorage.theme = "dark"
        localStorage.setItem('color-theme', 'dark');
    }
});