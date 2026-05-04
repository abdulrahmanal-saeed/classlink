(function () {
  function getCookie(name) {
    return document.cookie.split('; ').find(row => row.startsWith(name + '='))?.split('=')[1] || '';
  }
  function setCookie(name, value, days) {
    var expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
  }
  function uuid() {
    return (crypto && crypto.randomUUID) ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2);
  }
  var url = new URL(window.location.href);
  var keys = ['partner','utm_source','utm_medium','utm_campaign','utm_content','utm_term'];
  var hasTracking = keys.some(function (k) { return url.searchParams.get(k); });
  var visitor = getCookie('hn_media_visitor') || uuid();
  var session = getCookie('hn_media_session') || uuid();
  setCookie('hn_media_visitor', visitor, 365);
  setCookie('hn_media_session', session, 1);
  if (hasTracking) {
    var data = { first_touch_at: new Date().toISOString(), last_touch_at: new Date().toISOString(), landing_page: window.location.pathname + window.location.search };
    keys.forEach(function (k) { data[k] = url.searchParams.get(k) || ''; });
    if (!getCookie('hn_media_first_touch')) setCookie('hn_media_first_touch', JSON.stringify(data), 30);
    setCookie('hn_media_last_touch', JSON.stringify(data), 30);
    fetch('/api/media/track', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(Object.assign({}, data, { event_type: 'media_link_click', visitor_id: visitor, session_id: session, referrer: document.referrer }))
    }).catch(function () {});
  }
  window.HN_MEDIA_ATTRIBUTION = {
    visitor_id: visitor,
    session_id: session,
    first_touch: getCookie('hn_media_first_touch'),
    last_touch: getCookie('hn_media_last_touch')
  };
})();
