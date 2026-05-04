(function () {
  function getCookie(name) {
    return document.cookie.split('; ').find(row => row.startsWith(name + '='))?.split('=')[1];
  }
  function currentLang() {
    const url = new URL(window.location.href);
    return url.searchParams.get('lang') || getCookie('hn_lang') || document.documentElement.lang || 'en';
  }
  function applyLanguage(lang) {
    lang = lang === 'ar' ? 'ar' : 'en';
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.body.classList.toggle('is-rtl', lang === 'ar');
    document.body.classList.toggle('is-ltr', lang !== 'ar');
    const dict = (window.HN_I18N && window.HN_I18N[lang]) || {};
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      const key = el.getAttribute('data-i18n');
      if (dict[key]) el.textContent = dict[key];
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      const key = el.getAttribute('data-i18n-placeholder');
      if (dict[key]) el.setAttribute('placeholder', dict[key]);
    });
  }
  document.addEventListener('DOMContentLoaded', function () {
    applyLanguage(currentLang());
  });
})();
