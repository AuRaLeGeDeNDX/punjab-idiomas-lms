<!-- Hidden Google Translate Element -->
<div id="google_translate_element" style="display:none;"></div>

<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'en,es,ca,hi,pa',
            autoDisplay: false
        }, 'google_translate_element');
    }
    
    function setLanguage(lang, event) {
        if(event) event.preventDefault();
        
        let domain = window.location.hostname;
        if (lang === 'es') {
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=' + domain + '; path=/;';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=.' + domain + '; path=/;';
        } else {
            document.cookie = 'googtrans=/es/' + lang + '; path=/;';
            document.cookie = 'googtrans=/es/' + lang + '; domain=' + domain + '; path=/;';
            document.cookie = 'googtrans=/es/' + lang + '; domain=.' + domain + '; path=/;';
        }
        
        // Preserve current path, update query param
        let url = new URL(window.location.href);
        url.searchParams.set('lang', lang);
        window.location.href = url.href;
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
    /* Prevent body shift and hide all Google Translate UI elements */
    body { top: 0 !important; }
    .skiptranslate.goog-te-banner-frame { display: none !important; }
    #goog-gt-tt { display: none !important; }
    .goog-tooltip, .goog-tooltip:hover { display: none !important; }
    .goog-text-highlight { background-color: transparent !important; border: none !important; box-shadow: none !important; }
</style>
