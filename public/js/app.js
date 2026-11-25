// Menú móvil
document.addEventListener('click', function (e) {
  var toggle = e.target.closest('[data-menu-toggle]');
  if (toggle) {
    document.querySelector('[data-menu]').classList.toggle('abierto');
  }
  // Botones que piden confirmación antes de enviar el formulario
  var confirmar = e.target.closest('[data-confirmar]');
  if (confirmar && !window.confirm(confirmar.getAttribute('data-confirmar'))) {
    e.preventDefault();
  }
});

// Agregar más campos de archivo en el formulario del módulo
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-agregar-archivo]');
  if (!btn) return;
  e.preventDefault();
  var plantilla = document.querySelector('[data-plantilla-archivo]');
  var copia = plantilla.cloneNode(true);
  copia.removeAttribute('data-plantilla-archivo');
  copia.querySelectorAll('input').forEach(function (i) { i.value = ''; });
  plantilla.parentNode.insertBefore(copia, btn);
});
