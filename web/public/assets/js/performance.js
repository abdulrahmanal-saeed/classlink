(function () {
  // Phase 33: lightweight performance helpers. No heavy library dependency.

  // Lazy-load embedded videos only after the user asks to load them.
  document.addEventListener('click', function (event) {
    var button = event.target.closest('.perf-video-load');
    if (!button) return;
    var shell = button.closest('.perf-video-shell');
    if (!shell || shell.dataset.loaded === '1') return;
    var src = shell.getAttribute('data-video-src');
    if (!src) return;
    var iframe = document.createElement('iframe');
    iframe.src = src;
    iframe.title = button.getAttribute('data-title') || 'Video';
    iframe.loading = 'lazy';
    iframe.allowFullscreen = true;
    iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
    shell.innerHTML = '';
    shell.appendChild(iframe);
    shell.dataset.loaded = '1';
  });

  // Debounce search fields that submit their parent form.
  document.querySelectorAll('.perf-debounce-search').forEach(function (input) {
    var timer = null;
    var delay = parseInt(input.getAttribute('data-debounce-ms') || '350', 10);
    input.addEventListener('input', function () {
      var form = input.closest('form');
      if (!form) return;
      window.clearTimeout(timer);
      timer = window.setTimeout(function () {
        if (input.value.length === 0 || input.value.length >= 2) form.requestSubmit ? form.requestSubmit() : form.submit();
      }, delay);
    });
  });

  // Mark images without explicit loading attribute as lazy when they are not likely to be hero images.
  document.querySelectorAll('img:not([loading])').forEach(function (img, index) {
    if (index > 1) img.setAttribute('loading', 'lazy');
    if (!img.getAttribute('decoding')) img.setAttribute('decoding', 'async');
  });
})();
