document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('color-theme') === 'dark' ||
        (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.getElementById('theme-toggle-light-icon').classList.remove('hidden');
        document.getElementById('theme-toggle-dark-icon').classList.add('hidden');
    } else {
        document.getElementById('theme-toggle-light-icon').classList.add('hidden');
        document.getElementById('theme-toggle-dark-icon').classList.remove('hidden');
    }

    document.getElementById('theme-toggle').addEventListener('click', function() {
        document.getElementById('theme-toggle-dark-icon').classList.toggle('hidden');
        document.getElementById('theme-toggle-light-icon').classList.toggle('hidden');

        if (document.body.classList.contains('dark')) {
            document.body.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.body.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    });
});
