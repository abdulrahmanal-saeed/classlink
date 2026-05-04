(function () {
  document.addEventListener('click', function (event) {
    var target = event.target.closest('[data-confirm-message]');
    if (!target) return;
    var message = target.getAttribute('data-confirm-message') || 'Are you sure?';
    if (!window.confirm(message)) {
      event.preventDefault();
      event.stopPropagation();
    }
  });
})();
