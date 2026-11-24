const menuBtn = document.getElementById('menu');
const dropdown = document.getElementById('dropdown');

menuBtn.addEventListener('click', () => {
  dropdown.classList.toggle('open');
});

document.addEventListener('click', (e) => {
  if (!dropdown.contains(e.target) && !menuBtn.contains(e.target)) {
    dropdown.classList.remove('open');
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') dropdown.classList.remove('open');
});