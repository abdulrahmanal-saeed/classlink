(function () {
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add('motion-page-ready');
  if (reduceMotion) {
    document.documentElement.classList.add('motion-reduced');
    return;
  }

  var selectors = [
    'section .foundation-card',
    'section .status-box',
    '.dashboard-card',
    '.kpi-card',
    '.progress-card',
    '.testimonial-card',
    '.article-card',
    '.video-card'
  ];

  var nodes = [];
  selectors.forEach(function (selector) {
    document.querySelectorAll(selector).forEach(function (node) {
      if (!node.classList.contains('motion-reveal')) {
        node.classList.add('motion-reveal');
        nodes.push(node);
      }
    });
  });

  if (!('IntersectionObserver' in window)) {
    nodes.forEach(function (node) { node.classList.add('is-visible'); });
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

  nodes.forEach(function (node) { observer.observe(node); });
})();
